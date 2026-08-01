<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Terms and Conditions</title>
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
  <div class="terms-title">Terms and Conditions</div>

  <div class="terms-content">
    <h4>Your Agreement</h4>
    <p><strong>Last Revised:</strong> May 24, 2025</p>
    <p>
      1. YOUR AGREEMENT
      By accessing or using Easy Find, you agree to be bound by these Terms and Conditions. If you do not agree to any part of these terms, you may not use our services.
      We reserve the right to modify these Terms at any time. Updates will be effective immediately upon posting. It is your responsibility to review them regularly. Continued use of Easy Find after changes means you accept the updated Terms.
    </p>
    <hr>
    <p>
      2. PRIVACY POLICY
      Your privacy is important to us. Please review our <a href="{{route('privacy')}}">[Privacy Policy]</a> to understand how we collect, use, and protect your information. By using Easy Find, you also agree to the practices described in the Privacy Policy.
    </p>
    <hr>
    <p>
    3. Use of the Platform
    Easy Find provides a platform to connect property owners, buyers, renters, and agents. You agree to:
    <ul>
    <li>Provide accurate and complete information when creating an account or listing.</li>
    <li>Not misuse the platform for fraudulent or illegal activities.</li>
    <li>Use the service in compliance with all applicable laws.</li>
    </ul>
    We may suspend or terminate your account if we believe you have violated these terms.
    </p>
    <hr>
    <p>
    4. Property Listings
    All property listings must:
    <ul>
    <li>Be real and up-to-date.</li>
    <li>Include accurate information and pricing.</li>
    <li>Respect intellectual property rights (e.g., no stolen photos).</li>
    </ul>
    We reserve the right to remove or moderate listings that violate our guidelines.
    </p>
    <hr>
    <p>
     5. Third-Party Links
     Easy Find may contain links to third-party websites. These are provided for convenience only. We do not control or endorse these sites and are not responsible for their content or practices.
    </p>
    <hr>
    <p>
     6. Disclaimer
    Easy Find does not guarantee the accuracy or availability of property listings. We are not responsible for transactions or disputes between users.
    </p>
    <hr>
    <p>
      7. Contact If you have any questions about these Terms and Conditions, please contact us at:
<a href="#" onclick="openGmailCompose(event, 'easyfind43@gmail.com', 'Question about Terms'); return false;">easyfind43@gmail.com</a>     
    </p>
    <hr>
  </div>

  <div class="confirmation">
    <input type="checkbox" id="agreeCheckbox"> 
    <label for="agreeCheckbox"><strong>I confirm that I have read and accept the terms and conditions and privacy policy.</strong></label>
  </div>

  <div class="buttons">
    <button class="btn btn-cancel" id="cancelButton">Cancel</button>
    <button class="btn btn-accept" id="acceptButton" disabled>Accept</button> 
  </div>
</div>

<script>
  function openGmailCompose(event, to, subject) {
    event.preventDefault();
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(to)}&su=${encodeURIComponent(subject)}`;
    window.open(gmailUrl, '_blank');
  }

  
  const agreeCheckbox = document.getElementById('agreeCheckbox');
  const acceptButton = document.getElementById('acceptButton');
  const cancelButton = document.getElementById('cancelButton');


  agreeCheckbox.addEventListener('change', function() {
    if (this.checked) {
      acceptButton.disabled = false;
    } else {
      acceptButton.disabled = true;
    }
  });

  acceptButton.addEventListener('click', function() {
    if (!this.disabled) { 
      alert('Terms Accepted! Redirecting...'); 
      window.location.href = '/register'; 
    }
  });

  cancelButton.addEventListener('click', function() {
    alert('Action Cancelled. Redirecting...'); 

    window.location.href = '/'; 
  });

</script>
</body>
</html>