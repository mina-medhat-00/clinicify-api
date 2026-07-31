<!DOCTYPE html>
<html>
<head>
    <title>Cancelling My Appointment</title>
    <style>
        /* Basic styles for the email */
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        /* Container for the email content */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        /* Headline style */
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #eee;
        }

        /* Button styles */
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="email-container">
    <h1>Cancelling Confirmation</h1>

    <p>Dear {{$user2['name']}}</p>

    <p>I want to apologize for cancelling our appointment</p>

    <table>
        <tr>
            <th>name</th>
            <td>{{$user1['name']}}</td>
        </tr>
        <tr>
            <th>Date of meeting</th>
            <td>{{$all_data['date']}}</td>
        </tr>
        <tr>
            <th>Time</th>
            <td>{{$all_data['bookedSlot']}}</td>
        </tr>
        <tr>
            <th>Cancelled from</th>
            <td>{{$all_data['cancelFrom']}}</td>
        </tr>
    </table>

    <p>If you need to make any changes to your booking, please contact us as soon as possible.</p>

    <p>Thank you for choosing our services, we look forward to serving you soon!</p>

    <a href="#" class="button">View Booking Details</a>
</div>
</body>
</html>
