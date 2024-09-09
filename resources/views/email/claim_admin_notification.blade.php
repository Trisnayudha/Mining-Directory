<!DOCTYPE html>
<html>

<head>
    <title>New Company Claim Submission</title>
</head>

<body>
    <h2>New Company Claim Submission</h2>
    <p>There is a new company claim submission with the following details:</p>
    <ul>
        <li><strong>Name:</strong> {{ $full_name }}</li>
        <li><strong>Position:</strong> {{ $position_title }}</li>
        <li><strong>Company Name:</strong> {{ $company_name }}</li>
        <li><strong>Email:</strong> {{ $email }}</li>
        <li><strong>Phone Number:</strong> {{ $company_phone_number }}</li>
        <li><strong>Category:</strong> {{ $company_category }}</li>
    </ul>
    <br>
    <p>Please review this submission at your earliest convenience.</p>
    <br>
    <p>Best regards,</p>
    <p>Indonesia Miner Team</p>
</body>

</html>
