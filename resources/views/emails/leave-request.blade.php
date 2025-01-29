<!DOCTYPE html>
<html>
<head>
    <title>Leave Request</title>
</head>
<body>
    <h2>Leave Request Details</h2>
    <p>Employee Name: {{ $leaveData['user_name'] }}</p>
    <p>Date: {{ $leaveData['date'] }}</p>
    <p>Reason: {{ $leaveData['reason'] }}</p>
    <p>Status: {{ $leaveData['status'] }}</p>
</body>
</html>