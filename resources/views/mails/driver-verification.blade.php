<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <title>Hitchmail</title>
</head>

<body style="margin: 0; padding: 0; background-color: #1E1E1E;">
    <table align="center" width="100%" style="border-spacing: 0; border-collapse: collapse; background-color: #1E1E1E;">
        <tr>
            <td align="center">
                <!-- Outer Wrapper -->
                <table align="center" width="100%"
                    style="max-width: 600px; border-spacing: 0; border-collapse: collapse; background-color: #FFFFFF;">

                    <!-- Header Section -->
                    <tr>
                        <td style="padding: 20px; text-align: center;">
                            <img src="{{asset('Admin/images/logo.png')}}" alt="logo"
                                style="display: block; margin-bottom: 15px; max-width: 100%; height: auto;">
                        </td>
                    </tr>

                    <!-- Background Section -->
                    <tr>
                        <td align="center" style="position: relative; height: 190px;">
                            <table width="93%" height="190" style="">
                                <tr>
                                    <td align="center"
                                        style=" border-radius: 10px; padding: 12px; border-spacing: 0; border-collapse: collapse; background-image: url('./images/approved-bg.png'); background-size: cover; background-position: center;">
                                        <img src="{{asset('Admin/images/app-icon.png')}}" alt="msg-icon"
                                            style="display: block; margin-bottom: 15px;">
                                        <p style="color: #1E1E1E; font-size: 18px; font-weight: 500; margin: 0;">Hello
                                            {{ $name }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Welcome Section -->
                    <tr>
                        <td align="center" style="padding: 10px;">
                            <h2 style="color: #333333; font-size: 22px; font-weight: 600; margin: 10px 0;">Profile
                                Approved</h2>
                            <p style="color: #6B6B6B; font-size: 14px; font-weight: 400; margin: 0;">Congratulations!
                                Your Profile Has Been Approved
                            </p>
                        </td>
                    </tr>

                    <!-- Message Section -->
                    <tr>
                        <td style="padding: 30px; background-color: #18ABE3;">
                            <p
                                style="color: #FFFFFF; font-size: 18px; font-weight: 500; margin: 0; text-align: center;">
                                Welcome Aboard!</p>
                            <p style="color: #FFFFFF; font-size: 14px; font-weight: 400; margin: 10px 0; text-align: center;">
                                We are excited to inform you that your profile has been successfully approved! You are
                                now ready to start taking trips and earning with us. <br><br>
                                To get started, please log in to the *Driver App* and ensure everything is set up. You
                                can begin accepting bookings and managing your trips right away. <br><br>
                                If you have any questions or need assistance, feel free to reach out to our support team
                                at our support email.
                            </p>
                        </td>
                    </tr>

                    <!-- Instruction Section -->
                    <tr>
                        <td style="padding: 30px; text-align: center;">
                            <p style="color: #333333; font-size: 14px; font-weight: 400; margin: 0;">
                                🎉 Welcome aboard, and we wish you great success! 🎉
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Message Section -->
                    <tr>
                        <td style="padding: 0 20px 20px;">
                            <table align="center" width="100%"
                                style="border-spacing: 0; border-collapse: collapse; background-color: #EFEFEF;  border-radius: 10px;">
                                <tr>
                                    <td align="center"
                                        style="color: #6B6B6B; font-size: 14px; font-weight: 400; text-align: center; padding: 20px;">
                                        <span style="color: #19191A;">Best regards</span><br>
                                        The Hitchmail Team
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="background-color: #292929; height: 2px;"></td>
                    </tr>

                    <!-- Footer Links -->
                    <tr>
                        <td style="padding: 10px; text-align: center; background-color: #19191A;">
                            <a href="#"
                                style="color: #6C6C6C; font-size: 12px; font-weight: 400; text-decoration: underline;">
                                Privacy Policy
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>