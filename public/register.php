<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/send-mail.php';

$errors  = [];
$success = false;
$studentId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Honeypot
  if (!empty($_POST['website'])) {
    $success = true; // silently discard
  } else {

    // Rate limit: 3 per hour per session
    if (!isset($_SESSION['reg_count']) || time() - $_SESSION['reg_time'] > 3600) {
      $_SESSION['reg_count'] = 0;
      $_SESSION['reg_time']  = time();
    }
    if ($_SESSION['reg_count'] >= 3) {
      $errors[] = 'Too many registrations from this session. Please try again later.';
    } else {

      // Sanitize
      $firstName  = trim(strip_tags($_POST['first_name']  ?? ''));
      $lastName   = trim(strip_tags($_POST['last_name']   ?? ''));
      $dob        = $_POST['date_of_birth'] ?? '';
      $gender     = $_POST['gender']        ?? '';
      $whatsapp   = preg_replace('/[^0-9+\s\-]/', '', $_POST['whatsapp_number']        ?? '');
      $otherNum   = preg_replace('/[^0-9+\s\-]/', '', $_POST['other_number']            ?? '');
      $parentNum  = preg_replace('/[^0-9+\s\-]/', '', $_POST['parent_guardian_number'] ?? '');
      $parentName = trim(strip_tags($_POST['parent_guardian_name']  ?? ''));
      $parentEmail= filter_var(trim($_POST['parent_guardian_email'] ?? ''), FILTER_SANITIZE_EMAIL);
      $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
      $grade      = intval($_POST['grade']    ?? 0);
      $province   = trim(strip_tags($_POST['province']    ?? ''));
      $city       = trim(strip_tags($_POST['city']        ?? ''));
      $school     = trim(strip_tags($_POST['school_name'] ?? ''));
      $schoolOther= trim(strip_tags($_POST['school_other']?? ''));
      $progInterest = isset($_POST['programme_interest']) ? implode(', ', array_map('strip_tags', (array)$_POST['programme_interest'])) : '';
      $subjects   = isset($_POST['subjects']) ? implode(', ', array_map('strip_tags', (array)$_POST['subjects'])) : '';
      $howHeard   = trim(strip_tags($_POST['how_heard']   ?? ''));

      // Validate
      if (!preg_match('/^[a-zA-Z\s\'\-]{2,50}$/', $firstName))
        $errors[] = 'Please enter a valid first name.';
      if (!preg_match('/^[a-zA-Z\s\'\-]{2,50}$/', $lastName))
        $errors[] = 'Please enter a valid last name.';
      if (empty($dob)) {
        $errors[] = 'Date of birth is required.';
      } else {
        $age = (new DateTime())->diff(new DateTime($dob))->y;
        if ($age < 12 || $age > 22) $errors[] = 'Age must be between 12 and 22.';
      }
      if (!in_array($gender, ['Male', 'Female', 'Prefer not to say']))
        $errors[] = 'Please select your gender.';
      if (strlen(preg_replace('/\D/', '', $whatsapp)) < 10)
        $errors[] = 'Please enter a valid WhatsApp number.';
      if (strlen(preg_replace('/\D/', '', $parentNum)) < 10)
        $errors[] = 'Please enter a valid parent/guardian number.';
      if (!preg_match('/^[a-zA-Z\s\'\-]{2,80}$/', $parentName))
        $errors[] = 'Please enter a valid parent/guardian name.';
      if (!filter_var($parentEmail, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid parent/guardian email address.';
      if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid student email address.';
      if (!in_array($grade, [8,9,10,11]))
        $errors[] = 'Please select a valid grade (8–11).';
      if (empty($province)) $errors[] = 'Please select your province.';
      if (empty($city))     $errors[] = 'Please enter your city or town.';
      if (empty($school))   $errors[] = 'Please select your school.';
      if ($school === 'Other' && empty($schoolOther))
        $errors[] = 'Please enter your school name.';

      // Check duplicate email
      if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM valt_students WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) $errors[] = 'This email address is already registered.';
      }

      if (empty($errors)) {
        // Generate student ID
        $year  = date('Y');
        $count = $pdo->query('SELECT COUNT(*) FROM valt_students')->fetchColumn() + 1;
        $studentId = 'VALT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $finalSchool = ($school === 'Other') ? $schoolOther : $school;

        try {
          $stmt = $pdo->prepare('
            INSERT INTO valt_students
              (student_id, first_name, last_name, date_of_birth, gender,
               whatsapp_number, other_number, parent_guardian_number,
               parent_guardian_name, parent_guardian_email, email,
               grade, province, city, school_name, school_other,
               programme_interest, subjects_interest, how_heard)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ');
          $stmt->execute([
            $studentId, $firstName, $lastName, $dob, $gender,
            $whatsapp, $otherNum ?: null, $parentNum,
            $parentName, $parentEmail, $email,
            $grade, $province, $city, $finalSchool,
            ($school === 'Other' ? $schoolOther : null),
            $progInterest ?: null, $subjects ?: null, $howHeard ?: null
          ]);

          $_SESSION['reg_count']++;

          // Send emails
          sendRegistrationConfirmation($email, $firstName, $studentId);
          sendAdminNotification([
            'student_id'       => $studentId,
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'email'            => $email,
            'grade'            => $grade,
            'whatsapp_number'  => $whatsapp,
            'school_name'      => $finalSchool,
            'province'         => $province,
            'programme_interest' => $progInterest,
          ]);

          $success = true;

        } catch (PDOException $e) {
          error_log('Registration DB error: ' . $e->getMessage());
          $errors[] = 'Registration failed. Please try again.';
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — VALT Academy</title>
  <link rel="icon" href="img/fav.png">
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/linearicons.css">
  <link rel="stylesheet" href="css/font-awesome.min.css">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/valt-theme.css">
  <link rel="stylesheet" href="css/register.css">
</head>
<body>

  <!-- Header -->
  <header id="header">
    <div class="container main-menu">
      <div class="row align-items-center justify-content-between d-flex">
        <div id="logo">
          <a href="index.html"><img src="img/logo02.png" alt="VALT Academy" style="height:65px;"></a>
        </div>
        <div class="mobile-tagline">Educational enrichment provider</div>
        <nav id="nav-menu-container">
          <ul class="nav-menu">
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="courses.html">Programmes</a></li>
            <li><a href="gallery.html">Gallery</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="register.php" class="nav-register-btn" target="_blank">Register</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <!-- Breadcrumb -->
  <section class="banner-area relative" style="position:relative;padding:100px 0 60px;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:url('img/headerbg01.jpg');background-size:cover;background-position:center;filter:blur(3px);transform:scale(1.05);"></div>
    <div style="position:absolute;inset:0;background:rgba(10,35,66,0.72);"></div>
    <div class="container" style="position:relative;z-index:1;">
      <div class="row d-flex align-items-center justify-content-center">
        <div class="col-lg-8 text-center">
          <h2 class="text-white" style="font-size:34px;font-weight:700;margin-bottom:10px;">Student Registration</h2>
          <p class="text-white" style="opacity:0.85;font-size:15px;">Join the VALT community — fill in your details below to get started.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Registration Section -->
  <section class="register-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">

          <?php if ($success): ?>
          <!-- Success State -->
          <div class="register-card">
            <div class="register-success">
              <div class="success-icon"><i class="fa fa-check"></i></div>
              <h2>Registration Successful!</h2>
              <p>Welcome to VALT Academy! Your details have been received. Check your email for a confirmation message.</p>
              <a href="courses.html" class="btn-reg-next" style="margin-top:24px;display:inline-block;text-decoration:none;">Explore Programmes &rarr;</a>
            </div>
          </div>

          <?php else: ?>
          <!-- Registration Form -->
          <div class="register-card">

            <!-- Step Indicator -->
            <div class="step-indicator">
              <div class="steps">
                <div class="step-item active" data-step="1">
                  <div class="step-circle">1</div>
                  <div class="step-label">Personal</div>
                </div>
                <div class="step-item" data-step="2">
                  <div class="step-circle">2</div>
                  <div class="step-label">Contact</div>
                </div>
                <div class="step-item" data-step="3">
                  <div class="step-circle">3</div>
                  <div class="step-label">Academic</div>
                </div>
                <div class="step-item" data-step="4">
                  <div class="step-circle">4</div>
                  <div class="step-label">Interests</div>
                </div>
              </div>
              <div class="progress-bar-wrap">
                <div class="progress-fill" style="width:0%"></div>
              </div>
            </div>

            <div class="register-body">

              <?php if (!empty($errors)): ?>
              <div class="server-errors">
                <?php foreach ($errors as $e): ?>
                  <p><i class="fa fa-exclamation-circle"></i> <?php echo $e; ?></p>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <form id="registerForm" method="POST" action="register.php" novalidate>
                <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

                <!-- ====== STEP 1: Personal Details ====== -->
                <div class="step-pane active" data-step="1">
                  <div class="section-title">
                    <h3>Personal Details</h3>
                    <p>Tell us a little about yourself.</p>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>First Name <span class="required">*</span></label>
                        <input class="reg-input" type="text" name="first_name" required placeholder="e.g. Thabo" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Last Name <span class="required">*</span></label>
                        <input class="reg-input" type="text" name="last_name" required placeholder="e.g. Mokoena" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Date of Birth <span class="required">*</span></label>
                        <input class="reg-input" type="date" name="date_of_birth" required max="<?php echo date('Y-m-d', strtotime('-12 years')); ?>" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Gender <span class="required">*</span></label>
                        <div class="select-wrap">
                          <select class="reg-select" name="gender" required>
                            <option value="">-- Select --</option>
                            <option value="Male"   <?php echo (($_POST['gender'] ?? '') === 'Male')   ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Prefer not to say" <?php echo (($_POST['gender'] ?? '') === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                          </select>
                        </div>
                        <div class="field-error"></div>
                      </div>
                    </div>
                  </div>
                  <div class="form-nav" style="justify-content:flex-end;">
                    <button type="button" class="btn-reg-next">Next: Contact Info &rarr;</button>
                  </div>
                </div>

                <!-- ====== STEP 2: Contact Information ====== -->
                <div class="step-pane" data-step="2">
                  <div class="section-title">
                    <h3>Contact Information</h3>
                    <p>How can we reach you and your parent/guardian?</p>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Student's WhatsApp Number <span class="required">*</span></label>
                        <input class="reg-input" type="tel" name="whatsapp_number" required placeholder="e.g. 071 234 5678" value="<?php echo htmlspecialchars($_POST['whatsapp_number'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Student's Other Number <small style="color:#aab;font-weight:400;">(optional)</small></label>
                        <input class="reg-input" type="tel" name="other_number" placeholder="e.g. 031 555 0000" value="<?php echo htmlspecialchars($_POST['other_number'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Student's Email Address <span class="required">*</span></label>
                        <input class="reg-input" type="email" name="email" required placeholder="e.g. thabo@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Parent / Guardian Full Name <span class="required">*</span></label>
                        <input class="reg-input" type="text" name="parent_guardian_name" required placeholder="e.g. Sarah Mokoena" value="<?php echo htmlspecialchars($_POST['parent_guardian_name'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Parent / Guardian Phone Number <span class="required">*</span></label>
                        <input class="reg-input" type="tel" name="parent_guardian_number" required placeholder="e.g. 082 456 7890" value="<?php echo htmlspecialchars($_POST['parent_guardian_number'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Parent / Guardian Email Address <span class="required">*</span></label>
                        <input class="reg-input" type="email" name="parent_guardian_email" required placeholder="e.g. sarah@email.com" value="<?php echo htmlspecialchars($_POST['parent_guardian_email'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                  </div>
                  <div class="form-nav">
                    <button type="button" class="btn-reg-prev">&larr; Back</button>
                    <button type="button" class="btn-reg-next">Next: Academic Info &rarr;</button>
                  </div>
                </div>

                <!-- ====== STEP 3: Academic Information ====== -->
                <div class="step-pane" data-step="3">
                  <div class="section-title">
                    <h3>Academic Information</h3>
                    <p>Tell us about your school and grade.</p>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Current Grade <span class="required">*</span></label>
                        <div class="select-wrap">
                          <select class="reg-select" name="grade" required>
                            <option value="">-- Select Grade --</option>
                            <option value="8"  <?php echo (($_POST['grade'] ?? '') == '8')  ? 'selected' : ''; ?>>Grade 8</option>
                            <option value="9"  <?php echo (($_POST['grade'] ?? '') == '9')  ? 'selected' : ''; ?>>Grade 9</option>
                            <option value="10" <?php echo (($_POST['grade'] ?? '') == '10') ? 'selected' : ''; ?>>Grade 10</option>
                            <option value="11" <?php echo (($_POST['grade'] ?? '') == '11') ? 'selected' : ''; ?>>Grade 11</option>
                          </select>
                        </div>
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>Province <span class="required">*</span></label>
                        <div class="select-wrap">
                          <select class="reg-select" name="province" id="province" required>
                            <option value="">-- Select Province --</option>
                            <?php
                            $provinces = ['Gauteng','KwaZulu-Natal','Western Cape','Eastern Cape','Free State','Limpopo','Mpumalanga','North West','Northern Cape'];
                            foreach ($provinces as $p):
                              $sel = (($_POST['province'] ?? '') === $p) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $p; ?>" <?php echo $sel; ?>><?php echo $p; ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>City / Town <span class="required">*</span></label>
                        <input class="reg-input" type="text" name="city" required placeholder="e.g. Durban" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>School Name <span class="required">*</span></label>
                        <div class="select-wrap">
                          <select class="reg-select" name="school_name" id="school_name" required>
                            <option value="">-- Select Province First --</option>
                          </select>
                        </div>
                        <div class="field-error"></div>
                      </div>
                    </div>
                    <div class="col-12" id="school_other_wrap" style="display:none;">
                      <div class="reg-form-group">
                        <label>Enter Your School Name <span class="required">*</span></label>
                        <input class="reg-input" type="text" name="school_other" id="school_other" placeholder="Full name of your school" value="<?php echo htmlspecialchars($_POST['school_other'] ?? ''); ?>">
                        <div class="field-error"></div>
                      </div>
                    </div>
                  </div>
                  <div class="form-nav">
                    <button type="button" class="btn-reg-prev">&larr; Back</button>
                    <button type="button" class="btn-reg-next">Next: Interests &rarr;</button>
                  </div>
                </div>

                <!-- ====== STEP 4: Interests ====== -->
                <div class="step-pane" data-step="4">
                  <div class="section-title">
                    <h3>Your Interests</h3>
                    <p>Help us understand what excites you most.</p>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <div class="reg-form-group">
                        <label>Programme Interest <small style="color:#aab;font-weight:400;">(select all that apply)</small></label>
                        <div class="checkbox-group">
                          <?php
                          $programmes = [
                            'VALT 101 - Financial Literacy',
                            'VALT 102 - Entrepreneurship',
                            'VALT 103 - Emotional Intelligence',
                            'VALT 104 - Career Guidance',
                            'VALT 105 - Health & Fitness',
                            'VALT 106 - Internship Programme',
                            'Not sure yet',
                          ];
                          $selectedProgs = isset($_POST['programme_interest']) ? (array)$_POST['programme_interest'] : [];
                          foreach ($programmes as $prog):
                          ?>
                          <label class="checkbox-item">
                            <input type="checkbox" name="programme_interest[]" value="<?php echo htmlspecialchars($prog); ?>" <?php echo in_array($prog, $selectedProgs) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($prog); ?>
                          </label>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="reg-form-group">
                        <label>How did you hear about VALT?</label>
                        <div class="select-wrap">
                          <select class="reg-select" name="how_heard">
                            <option value="">-- Select --</option>
                            <option value="Social Media">Social Media</option>
                            <option value="Friend or Family">Friend or Family</option>
                            <option value="School or Teacher">School or Teacher</option>
                            <option value="Online Search">Online Search</option>
                            <option value="Event or Workshop">Event or Workshop</option>
                            <option value="WhatsApp Group">WhatsApp Group</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="reg-form-group">
                        <label>Subjects You Enjoy at School <small style="color:#aab;font-weight:400;">(select all that apply)</small></label>
                        <div class="checkbox-group">
                          <?php
                          $subjectsList = ['Mathematics','Physical Science','Life Sciences','Accounting','Business Studies','Economics','History','Geography','English','Afrikaans','Art','Technology','Computer Applications','Life Orientation'];
                          $selectedSubs = isset($_POST['subjects']) ? (array)$_POST['subjects'] : [];
                          foreach ($subjectsList as $sub):
                          ?>
                          <label class="checkbox-item">
                            <input type="checkbox" name="subjects[]" value="<?php echo $sub; ?>" <?php echo in_array($sub, $selectedSubs) ? 'checked' : ''; ?>>
                            <?php echo $sub; ?>
                          </label>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-nav">
                    <button type="button" class="btn-reg-prev">&larr; Back</button>
                    <button type="submit" class="btn-reg-submit">&#10003; Submit Registration</button>
                  </div>
                </div>

              </form>
            </div>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-area section-gap">
    <div class="container">
      <div class="row">
        <div class="col-lg-5 col-md-6 col-sm-6">
          <div class="single-footer-widget">
            <h6 class="footer_title">About VALT</h6>
            <p>Empowering young adults and adults with lifelong skills. VALT is an educational enrichment provider offering future-ready learning experiences.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="single-footer-widget">
            <h4>Our Programmes</h4>
            <ul>
              <li><a href="courses.html">VALT 101 - Financial Literacy</a></li>
              <li><a href="courses.html">VALT 102 - Entrepreneurship</a></li>
              <li><a href="courses.html">VALT 103 - Emotional Intelligence</a></li>
              <li><a href="courses.html">VALT 104 - Career Guidance</a></li>
              <li><a href="courses.html">VALT 105 - Health &amp; Fitness</a></li>
              <li><a href="courses.html">VALT 106 - Internship Programme</a></li>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
          <div class="single-footer-widget">
            <h4>Contact Us</h4>
            <ul>
              <li><a href="https://wa.me/27614610828"><i class="fa fa-whatsapp"></i> 061 461 0828 (WhatsApp)</a></li>
              <li><a href="mailto:info@valt.co.za"><i class="fa fa-envelope"></i> info@valt.co.za</a></li>
              <li><i class="fa fa-map-marker"></i> Durban, KZN, South Africa</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer-bottom row align-items-center justify-content-between">
        <p class="footer-text m-0 col-lg-8">Copyright &copy;<script>document.write(new Date().getFullYear());</script> VALT Academy. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <div class="float-contact">
    <a href="https://wa.me/27614610828" class="float-whatsapp" target="_blank" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
    <a href="mailto:info@valt.co.za" class="float-email" aria-label="Email"><i class="fa fa-envelope"></i></a>
  </div>

  <script src="js/vendor/jquery-2.2.4.min.js"></script>
  <script src="js/vendor/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
  <script src="js/register.js"></script>
</body>
</html>
