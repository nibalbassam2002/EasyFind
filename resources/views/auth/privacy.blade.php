<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Privacy Policy</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f4f7f6; 
      margin: 0;
      padding: 20px; 
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh; 
      box-sizing: border-box;
    }
    .terms-container { 
      width: 100%;
      max-width: 700px; 
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 30px;
      display: flex;
      flex-direction: column;
    }
    .terms-title {
      color: #FFD700; 
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px; 
      text-align: center;
    }
    .terms-content {
      overflow-y: auto;
      max-height: 350px;
      border: 1px solid #e0e0e0; 
      border-radius: 5px;
      padding: 15px 20px;
      margin-bottom: 20px;
      line-height: 1.6; 
    }
    .terms-content h4 {
      margin-top: 0;
      color: #333;
    }
    .terms-content p {
      margin-bottom: 1em;
    }
    .terms-content hr {
      border: none;
      border-top: 1px solid #eee;
      margin: 15px 0;
    }
    .terms-content a {
        color: #DAA520; 
        text-decoration: none;
    }
    .terms-content a:hover {
        text-decoration: underline;
    }
    .confirmation {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 25px; 
    }
    .confirmation input[type="checkbox"] {
      accent-color: #FFD700;
      width: 18px; 
      height: 18px;
    }
    .confirmation label {
        font-size: 0.95em;
        color: #333;
    }
    .buttons {
      display: flex;
      justify-content: flex-end; 
      gap: 15px; 
    }
    .btn {
      padding: 10px 25px; 
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1em;
      transition: background-color 0.2s ease, color 0.2s ease;
    }
    .btn-cancel {
      background: none;
      color: #FFD700; 
      border: 1px solid #FFD700; 
    }
    .btn-cancel:hover {
        background-color: #fffaf0; 
    }
    .btn-accept {
      background-color: #FFD700; 
      color: #333;
    }
    .btn-accept:hover {
        background-color: #e6c300; 
    }
    .btn-accept:disabled {
        background-color: #cccccc;
        color: #666666;
        cursor: not-allowed;
        border-color: #cccccc;
    }
  </style>
</head>
<body>

<div class="terms-container">
  <div class="terms-title">Privacy Policy</div>

  <div class="terms-content">
    <h4>Your Agreement</h4> 
    <p><strong>Last Revised:</strong> May 24, 2025</p>
    <p>
        1. Information We Collect :
    <br>
    We may collect the following types of information:
    <ul>
        <li>Personal Information: Name, email address, phone number, location.</li>
        <li>Property Details: Listings you submit, preferences, search filters.</li>
        <li>Usage Data: IP address, browser type, pages visited, and time spent.</li>
        <li>Device Data: Mobile device ID, OS, app version.</li>
    </ul>
    </p>
    <hr>
    <p>
   2. How We Use Your Information
   <br>
   We use the information we collect to:
    <ul>
        <li>Provide and improve our services.</li>
        <li>Personalize your experience on Easy Find.</li>
        <li>Communicate with you (e.g., updates, promotions, support).</li>
        <li>Ensure security and prevent fraud.</li>
        <li>Comply with legal requirements.</li>
    </ul>
    </p>
    <hr>
    <p>
    3. Sharing Your Information
    <br>
   We do not sell your personal data. However, we may share information:
    <ul>
        <li>With service providers (e.g., cloud hosting, analytics) under confidentiality agreements.</li>
        <li>With other users when necessary (e.g., when contacting a property owner).</li>
        <li>When required by law or legal process.</li>
    </ul>
    </p>
    <hr>
    <p>
   4. Cookies and Tracking
    <br>
   We use cookies and similar tracking tools to:
    <ul>
        <li>Remember your preferences.</li>
        <li>Analyze traffic and usage.</li>
        <li>Enhance the user experience.</li>
    </ul>
    You can control cookie settings through your browser.
    </p>
    <hr>
    <p>
   5. Data Security
    <br>
   We implement strong security measures to protect your data. However, no system is 100% secure. Use the platform at your own risk.
    </p>
    <hr>
    <p>
    6. Your Rights
    <br>
   Depending on your location, you may have the right to:
    <ul>
        <li>Access the data we hold about you.</li>
        <li>Request corrections or deletions.</li>
        <li>Opt out of marketing communications.</li>
    </ul>
    To exercise your rights, contact us at <a href="#" onclick="openGmailCompose(event, 'easyfind43@gmail.com', 'Question about Terms'); return false;">easyfind43@gmail.com</a>
    </p>
    <hr>
    <p>
    7. Third-Party Links
    <br>
    Easy Find may include links to external websites. We are not responsible for their privacy practices.
    </p>
    <hr>
    <p>
    8. Changes to This Policy
    <br>
    We may update this policy from time to time. Any changes will be posted here with a new "Last Updated" date.
    </p>
    <hr>
    <p>
    9. Contact Us
    <br>
    If you have any questions or concerns about this Privacy Policy, you can reach us at:
    <br>
    <a href="#" onclick="openGmailCompose(event, 'easyfind43@gmail.com', 'Question about Terms'); return false;">easyfind43@gmail.com</a>
    </p>
    <hr>
  </div>

  <div class="confirmation">
    <input type="checkbox" id="agreePrivacyCheckbox"> 
    <label for="agreePrivacyCheckbox"><strong>I confirm that I have read and accept this privacy policy.</strong></label> <!-- Adjusted label text slightly -->
  </div>

  <div class="buttons">
    <button class="btn btn-cancel" id="cancelPrivacyButton">Cancel</button>
    <button class="btn btn-accept" id="acceptPrivacyButton" disabled>Accept</button>
  </div>
</div>

<script>
 
  function openGmailCompose(event, to, subject) {
    event.preventDefault();
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(to)}&su=${encodeURIComponent(subject)}`;
    window.open(gmailUrl, '_blank');
  }

  const agreePrivacyCheckbox = document.getElementById('agreePrivacyCheckbox');
  const acceptPrivacyButton = document.getElementById('acceptPrivacyButton');
  const cancelPrivacyButton = document.getElementById('cancelPrivacyButton');

  if (agreePrivacyCheckbox) { 
    agreePrivacyCheckbox.addEventListener('change', function() {
      acceptPrivacyButton.disabled = !this.checked;
    });
  }

  if (acceptPrivacyButton) {
    acceptPrivacyButton.addEventListener('click', function() {
      if (!this.disabled) {
        alert('Privacy Policy Accepted! Redirecting...'); 

        window.location.href = '/register'; 
      }
    });
  }

  if (cancelPrivacyButton) {
    cancelPrivacyButton.addEventListener('click', function() {
      alert('Action Cancelled. Redirecting...'); 

  
      window.location.href = '/'; 
    });
  }
</script>
</body>
</html>