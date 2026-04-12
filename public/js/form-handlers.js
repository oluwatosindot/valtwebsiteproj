// -------  VALT Form Handlers (Enquiry + Newsletter)

$(document).ready(function () {

  // Programme Enquiry Form
  $('.form-wrap').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $form.find('button[type="submit"], .primary-btn');
    var originalText = $btn.text();

    var data = {
      name: $form.find('input[name="name"]').val(),
      phone: $form.find('input[name="phone"]').val(),
      email: $form.find('input[name="email"]').val(),
      programme: $form.find('select').find(':selected').text()
    };

    if (!data.name || !data.email) {
      alert('Please fill in your name and email.');
      return;
    }

    $btn.text('Sending...');

    $.ajax({
      url: 'enquiry.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function (res) {
        alert(res.message || 'Enquiry submitted successfully!');
        $form.trigger('reset');
        // Reset nice-select if present
        $form.find('.nice-select .current').text('Choose Programme');
      },
      error: function () {
        alert('Something went wrong. Please try again.');
      },
      complete: function () {
        $btn.text(originalText);
      }
    });
  });

  // Newsletter Subscribe
  $('.newsletter-widget .bbtns').on('click', function (e) {
    e.preventDefault();
    var $widget = $(this).closest('.newsletter-widget');
    var $input = $widget.find('input');
    var email = $input.val().trim();

    if (!email) {
      alert('Please enter your email address.');
      return;
    }

    var $btn = $(this);
    var originalText = $btn.text();
    $btn.text('Subscribing...');

    $.ajax({
      url: '/api/newsletter',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ email: email }),
      success: function (res) {
        alert(res.message || 'Subscribed successfully!');
        $input.val('');
      },
      error: function () {
        alert('Something went wrong. Please try again.');
      },
      complete: function () {
        $btn.text(originalText);
      }
    });
  });

});
