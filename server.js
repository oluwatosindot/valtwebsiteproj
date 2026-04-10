require('dotenv').config();

const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const path = require('path');
const nodemailer = require('nodemailer');
const rateLimit = require('express-rate-limit');
const pool = require('./config/database');

const app = express();
const PORT = process.env.PORT || 3000;

// --- Utility: strip HTML tags to prevent XSS in emails/DB ---
function sanitize(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#x27;');
}

// --- Utility: basic email format check ---
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Security headers (CSP, X-Frame-Options, HSTS, nosniff, Referrer-Policy, etc.)
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc:  ["'self'"],
      scriptSrc:   ["'self'"],
      styleSrc:    ["'self'", 'fonts.googleapis.com', "'unsafe-inline'"],
      fontSrc:     ["'self'", 'fonts.gstatic.com'],
      imgSrc:      ["'self'", 'data:'],
      connectSrc:  ["'self'"],
      frameSrc:    ["'none'"],
      objectSrc:   ["'none'"],
    },
  },
}));
app.disable('x-powered-by');

// Middleware
app.use(cors({
  origin: process.env.CORS_ORIGIN || 'http://localhost:3000',
  methods: ['GET', 'POST'],
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Rate limiting for API endpoints
const apiLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 10, // 10 requests per window per IP
  message: 'Too many submissions. Please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});

const newsletterLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 5,
  message: { success: false, message: 'Too many attempts. Please try again later.' },
  standardHeaders: true,
  legacyHeaders: false,
});

// Static files — serve ONLY the public/ subfolder, not the project root
app.use(express.static(path.join(__dirname, 'public')));

// Email transporter
const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST,
  port: parseInt(process.env.SMTP_PORT) || 587,
  secure: process.env.SMTP_SECURE === 'true',
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASSWORD
  }
});

// ==================== API Routes ====================

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'OK', timestamp: new Date().toISOString() });
});

// POST /api/contact - Contact form (returns plain text for mail-script.js compatibility)
app.post('/api/contact', apiLimiter, async (req, res) => {
  try {
    const name = sanitize(req.body.name);
    const email = sanitize(req.body.email);
    const subject = sanitize(req.body.subject);
    const message = sanitize(req.body.message);

    if (!name || !email || !message) {
      return res.status(400).send('Please fill in all required fields.');
    }

    if (!isValidEmail(req.body.email)) {
      return res.status(400).send('Please enter a valid email address.');
    }

    // Save to database
    await pool.execute(
      'INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)',
      [name, email, subject, message]
    );

    // Send notification email to VALT
    try {
      await transporter.sendMail({
        from: `"${process.env.SMTP_FROM_NAME}" <${process.env.SMTP_FROM_EMAIL}>`,
        to: process.env.CONTACT_EMAIL,
        subject: `New Contact: ${subject || 'No Subject'}`,
        html: `
          <h3>New Contact Form Submission</h3>
          <p><strong>Name:</strong> ${name}</p>
          <p><strong>Email:</strong> ${email}</p>
          <p><strong>Subject:</strong> ${subject || 'N/A'}</p>
          <p><strong>Message:</strong></p>
          <p>${message}</p>
        `
      });
    } catch (emailErr) {
      console.error('Email send failed:', emailErr.message);
    }

    // Send auto-reply confirmation to the sender
    try {
      await transporter.sendMail({
        from: `"${process.env.SMTP_FROM_NAME}" <${process.env.SMTP_FROM_EMAIL}>`,
        to: req.body.email,
        subject: 'Thank you for contacting VALT Academy',
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: #0a2342; padding: 30px; text-align: center;">
              <h1 style="color: #fff; margin: 0;">VALT Academy</h1>
            </div>
            <div style="padding: 30px; background: #f8f9fa;">
              <h2 style="color: #0a2342;">Thank you, ${name}!</h2>
              <p style="color: #555; line-height: 1.6;">We have received your message and will get back to you as soon as possible.</p>
              <p style="color: #555; line-height: 1.6;">Here's a copy of what you sent:</p>
              <div style="background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #2a9d8f;">
                <p><strong>Subject:</strong> ${subject || 'N/A'}</p>
                <p><strong>Message:</strong> ${message}</p>
              </div>
              <p style="color: #555; line-height: 1.6; margin-top: 20px;">Kind regards,<br><strong>The VALT Team</strong></p>
            </div>
            <div style="background: #0a2342; padding: 15px; text-align: center;">
              <p style="color: rgba(255,255,255,0.6); font-size: 12px; margin: 0;">&copy; VALT Academy. All rights reserved.</p>
            </div>
          </div>
        `
      });
    } catch (replyErr) {
      console.error('Auto-reply failed:', replyErr.message);
    }

    res.send('Message sent successfully! Check your email for a confirmation.');
  } catch (err) {
    console.error('Contact error:', err);
    res.status(500).send('Something went wrong. Please try again later.');
  }
});

// POST /api/enquiry - Programme enquiry form (returns JSON)
app.post('/api/enquiry', apiLimiter, async (req, res) => {
  try {
    const name = sanitize(req.body.name);
    const phone = sanitize(req.body.phone);
    const email = sanitize(req.body.email);
    const programme = sanitize(req.body.programme);

    if (!name || !email) {
      return res.status(400).json({ success: false, message: 'Name and email are required.' });
    }

    if (!isValidEmail(req.body.email)) {
      return res.status(400).json({ success: false, message: 'Please enter a valid email address.' });
    }

    // Save to database
    await pool.execute(
      'INSERT INTO enquiries (name, phone, email, programme) VALUES (?, ?, ?, ?)',
      [name, phone, email, programme]
    );

    // Send notification email to VALT
    try {
      await transporter.sendMail({
        from: `"${process.env.SMTP_FROM_NAME}" <${process.env.SMTP_FROM_EMAIL}>`,
        to: process.env.ENQUIRY_EMAIL,
        subject: `New Programme Enquiry: ${programme || 'General'}`,
        html: `
          <h3>New Programme Enquiry</h3>
          <p><strong>Name:</strong> ${name}</p>
          <p><strong>Phone:</strong> ${phone || 'N/A'}</p>
          <p><strong>Email:</strong> ${email}</p>
          <p><strong>Programme:</strong> ${programme || 'N/A'}</p>
        `
      });
    } catch (emailErr) {
      console.error('Email send failed:', emailErr.message);
    }

    // Auto-reply to enquirer
    try {
      await transporter.sendMail({
        from: `"${process.env.SMTP_FROM_NAME}" <${process.env.SMTP_FROM_EMAIL}>`,
        to: req.body.email,
        subject: `VALT Academy — Enquiry Received: ${programme || 'General'}`,
        html: `
          <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: #0a2342; padding: 30px; text-align: center;">
              <h1 style="color: #fff; margin: 0;">VALT Academy</h1>
            </div>
            <div style="padding: 30px; background: #f8f9fa;">
              <h2 style="color: #0a2342;">Hi ${name}!</h2>
              <p style="color: #555; line-height: 1.6;">Thank you for your interest in <strong>${programme || 'our programmes'}</strong>. We've received your enquiry and will be in touch shortly.</p>
              <p style="color: #555; line-height: 1.6;">Kind regards,<br><strong>The VALT Team</strong></p>
            </div>
            <div style="background: #0a2342; padding: 15px; text-align: center;">
              <p style="color: rgba(255,255,255,0.6); font-size: 12px; margin: 0;">&copy; VALT Academy. All rights reserved.</p>
            </div>
          </div>
        `
      });
    } catch (replyErr) {
      console.error('Auto-reply failed:', replyErr.message);
    }

    res.json({ success: true, message: 'Enquiry submitted successfully! Check your email for a confirmation.' });
  } catch (err) {
    console.error('Enquiry error:', err);
    res.status(500).json({ success: false, message: 'Something went wrong. Please try again later.' });
  }
});

// POST /api/newsletter - Newsletter signup (returns JSON)
app.post('/api/newsletter', newsletterLimiter, async (req, res) => {
  try {
    const email = sanitize(req.body.email);

    if (!email) {
      return res.status(400).json({ success: false, message: 'Email is required.' });
    }

    if (!isValidEmail(req.body.email)) {
      return res.status(400).json({ success: false, message: 'Please enter a valid email address.' });
    }

    // Save to database (handle duplicate)
    try {
      await pool.execute(
        'INSERT INTO newsletter_subscribers (email) VALUES (?)',
        [email]
      );
    } catch (dbErr) {
      if (dbErr.code === 'ER_DUP_ENTRY') {
        return res.json({ success: true, message: 'You are already subscribed!' });
      }
      throw dbErr;
    }

    res.json({ success: true, message: 'Subscribed successfully!' });
  } catch (err) {
    console.error('Newsletter error:', err);
    res.status(500).json({ success: false, message: 'Something went wrong. Please try again later.' });
  }
});

// Fallback: serve index.html for root
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start server
app.listen(PORT, () => {
  console.log(`VALT server running at http://localhost:${PORT}`);
});
