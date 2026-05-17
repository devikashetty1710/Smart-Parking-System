<?php
/**
 * Smart Parking System - Mailer Service Class
 * Implements standard SMTP communication via sockets and HTML email templates.
 */

include_once __DIR__ . "/../config/mail.php";

class Mailer {
    
    /**
     * Sends an HTML email using SMTP (sockets) or PHP default mail() fallback.
     */
    public static function sendHTML($to, $subject, $body) {
        if (defined('SEND_REAL_EMAILS') && SEND_REAL_EMAILS === true) {
            return self::sendViaSMTP($to, $subject, $body);
        } else {
            // Local fallback: default PHP mail function
            // In XAMPP, this is intercepted by mailtodisk and stored in xampp/mailoutput/
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
            $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            
            return mail($to, $subject, $body, $headers);
        }
    }

    /**
     * Custom lightweight SMTP socket mailer client.
     */
    private static function sendViaSMTP($to, $subject, $body) {
        $host = SMTP_HOST;
        $port = SMTP_PORT;
        $user = SMTP_USER;
        $pass = SMTP_PASS;
        $secure = strtolower(SMTP_SECURE);
        $from = SMTP_FROM_EMAIL;
        $fromName = SMTP_FROM_NAME;

        // Check credentials
        if (empty($user) || empty($pass)) {
            error_log("SMTP Error: Username or password not configured in config/mail.php");
            return false;
        }

        // Connection prefix for SSL
        $connectionPrefix = ($secure === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen($connectionPrefix . $host, $port, $errno, $errstr, 15);

        if (!$socket) {
            error_log("SMTP Connection Error: $errstr ($errno)");
            return false;
        }

        // Helper function to read SMTP responses
        $getResponse = function($socket) {
            $response = "";
            while (($line = fgets($socket, 512)) !== false) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            return $response;
        };

        // 1. Read Greeting
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return false;
        }

        // 2. EHLO
        fwrite($socket, "EHLO localhost\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '250') {
            fclose($socket);
            return false;
        }

        // 3. STARTTLS encryption handshake if TLS is requested
        if ($secure === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            $response = $getResponse($socket);
            if (substr($response, 0, 3) !== '220') {
                fclose($socket);
                return false;
            }

            // Enable crypto on the socket
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            // Check for newer TLS methods if supported
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            
            stream_context_set_option($socket, 'ssl', 'verify_peer', false);
            stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
            
            if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                fclose($socket);
                return false;
            }

            // Resend EHLO after encryption is established
            fwrite($socket, "EHLO localhost\r\n");
            $response = $getResponse($socket);
            if (substr($response, 0, 3) !== '250') {
                fclose($socket);
                return false;
            }
        }

        // 4. Authenticate
        fwrite($socket, "AUTH LOGIN\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '334') {
            fclose($socket);
            return false;
        }

        // Send username
        fwrite($socket, base64_encode($user) . "\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '334') {
            fclose($socket);
            return false;
        }

        // Send password
        fwrite($socket, base64_encode($pass) . "\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '235') {
            fclose($socket);
            return false;
        }

        // 5. Set sender and recipient
        fwrite($socket, "MAIL FROM: <$user>\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '250') {
            fclose($socket);
            return false;
        }

        fwrite($socket, "RCPT TO: <$to>\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '250') {
            fclose($socket);
            return false;
        }

        // 6. Data block
        fwrite($socket, "DATA\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '354') {
            fclose($socket);
            return false;
        }

        // Build professional standard headers
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$user>",
            "To: <$to>",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "Date: " . date('r'),
            "Message-ID: <" . uniqid('', true) . "@" . $host . ">",
            "X-Mailer: PHP-PureSMTP-Mailer",
            "", // Blank line separating headers from body
            $body
        ];

        // Send headers and body line by line
        $data = implode("\r\n", $headers);
        
        // SMTP dot-stuffing (ensure any leading dot has an extra dot)
        $data = preg_replace('/^\./m', '..', $data);
        
        fwrite($socket, $data . "\r\n.\r\n");
        $response = $getResponse($socket);
        if (substr($response, 0, 3) !== '250') {
            fclose($socket);
            return false;
        }

        // 7. Quit
        fwrite($socket, "QUIT\r\n");
        $getResponse($socket);
        fclose($socket);

        return true;
    }

    /**
     * Standard styled HTML layout container for emails.
     */
    private static function getEmailTemplate($title, $headerContent, $innerBody) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: 'Segoe UI', Arial, sans-serif;
                    background-color: #f1f5f9;
                    color: #1e293b;
                }
                .wrapper {
                    width: 100%;
                    table-layout: fixed;
                    background-color: #f1f5f9;
                    padding: 20px 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                    border: 1px solid #e2e8f0;
                }
                .header {
                    background: linear-gradient(135deg, #10b981, #059669);
                    padding: 30px 20px;
                    text-align: center;
                    color: #ffffff;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                }
                .content {
                    padding: 30px 25px;
                    line-height: 1.6;
                }
                .content p {
                    margin: 0 0 16px 0;
                    font-size: 15px;
                }
                .card {
                    background-color: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 25px 0;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0 15px 0;
                }
                .button {
                    background-color: #10b981;
                    color: #ffffff !important;
                    text-decoration: none;
                    padding: 12px 28px;
                    border-radius: 6px;
                    font-weight: 600;
                    display: inline-block;
                    font-size: 15px;
                    box-shadow: 0 2px 5px rgba(16, 185, 129, 0.2);
                    transition: background-color 0.2s;
                }
                .footer {
                    background-color: #f8fafc;
                    border-top: 1px solid #e2e8f0;
                    padding: 20px;
                    text-align: center;
                    font-size: 13px;
                    color: #64748b;
                }
                .footer p {
                    margin: 5px 0;
                }
                .table-details {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .table-details td {
                    padding: 8px 0;
                    border-bottom: 1px dashed #e2e8f0;
                    font-size: 14px;
                }
                .table-details td.label {
                    color: #64748b;
                    font-weight: 500;
                }
                .table-details td.value {
                    text-align: right;
                    font-weight: 600;
                    color: #1e293b;
                }
                .table-details tr:last-child td {
                    border-bottom: none;
                }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='container'>
                    <div class='header'>
                        <h1>{$headerContent}</h1>
                    </div>
                    <div class='content'>
                        {$innerBody}
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " SmartPark. All rights reserved.</p>
                        <p>This is an automated operational notification regarding your active account.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Send professional welcome notification.
     */
    public static function sendWelcomeEmail($to, $name) {
        $subject = "Welcome to SmartPark - Account Active!";
        
        $innerBody = "
            <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Welcome to <strong>SmartPark</strong>! We are thrilled to have you join our smart parking system. Your account is now fully active, and you are ready to book secure, real-time parking spaces with absolute ease.</p>
            
            <div class='card'>
                <h3 style='margin-top:0; color:#059669; font-size:16px;'>Getting Started is Simple:</h3>
                <ol style='margin: 0; padding-left: 20px; font-size:14px; color:#475569;'>
                    <li style='margin-bottom: 8px;'>Log in to your account dashboard.</li>
                    <li style='margin-bottom: 8px;'>Browse available spots across Downtown, Mall, or Business District zones.</li>
                    <li style='margin-bottom: 8px;'>Select your time slot, secure your spot with easy checkout, and park stress-free!</li>
                </ol>
            </div>

            <div class='button-container'>
                <a href='http://localhost/dbms_final/login.php' class='button'>Log In to Your Dashboard</a>
            </div>
            
            <p style='font-size:14px; margin-top:20px;'>If you have any questions or require assistance, our support team is available 24/7. Happy stress-free parking!</p>
            <p style='font-weight:600; margin-bottom:0;'>Best Regards,<br>The SmartPark Team</p>
        ";

        $html = self::getEmailTemplate("Welcome to SmartPark", "🚗 Welcome to SmartPark", $innerBody);
        return self::sendHTML($to, $subject, $html);
    }

    /**
     * Send professional reservation and billing invoice confirmation.
     */
    public static function sendBookingConfirmation($to, $name, $booking) {
        $subject = "Booking Confirmed - Ticket #" . $booking['reservation_id'];
        
        // Calculate nice variables
        $start = date('M d, Y h:i A', strtotime($booking['start_time']));
        $end = date('M d, Y h:i A', strtotime($booking['end_time']));
        $priceFormatted = "$" . number_format($booking['total_price'], 2);
        
        $innerBody = "
            <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Great news! Your parking reservation is officially confirmed. Your payment has been received successfully. Below is your detailed receipt and digital parking permit.</p>
            
            <div class='card' style='border-left: 4px solid #10b981;'>
                <h3 style='margin-top:0; margin-bottom: 15px; color:#059669; font-size:16px; display:flex; justify-content:space-between;'>
                    <span>Parking Permit</span>
                    <span style='background:#dcfce7; color:#059669; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600;'>PAID</span>
                </h3>
                <table class='table-details'>
                    <tr>
                        <td class='label'>Reservation ID</td>
                        <td class='value'>#{$booking['reservation_id']}</td>
                    </tr>
                    <tr>
                        <td class='label'>Parking Slot</td>
                        <td class='value' style='color:#10b981; font-size: 16px;'>{$booking['space_name']}</td>
                    </tr>
                    <tr>
                        <td class='label'>Location Zone</td>
                        <td class='value'>{$booking['location']}</td>
                    </tr>
                    <tr>
                        <td class='label'>Start Date/Time</td>
                        <td class='value'>{$start}</td>
                    </tr>
                    <tr>
                        <td class='label'>End Date/Time</td>
                        <td class='value'>{$end}</td>
                    </tr>
                    <tr style='font-size: 15px; font-weight: 700;'>
                        <td class='label' style='color:#1e293b; font-weight:700;'>Total Amount Paid</td>
                        <td class='value' style='color:#059669; font-size: 16px;'>{$priceFormatted}</td>
                    </tr>
                </table>
            </div>

            <div class='button-container'>
                <a href='http://localhost/dbms_final/user/dashboard.php' class='button'>View Booking Dashboard</a>
            </div>

            <h3 style='color:#1e293b; font-size:15px; margin-top: 25px;'>🔔 Useful Guidelines:</h3>
            <ul style='padding-left:20px; margin:0 0 20px 0; font-size:13px; color:#475569; line-height:1.7;'>
                <li style='margin-bottom:5px;'>Please ensure you park only within your designated space (<strong>{$booking['space_name']}</strong>).</li>
                <li style='margin-bottom:5px;'>Your digital permit is valid strictly within the designated start and end time.</li>
                <li style='margin-bottom:5px;'>To extend your parking duration, log in to your dashboard prior to your booking's expiration.</li>
            </ul>

            <p style='font-weight:600; margin-bottom:0;'>Best Regards,<br>The SmartPark Team</p>
        ";

        $html = self::getEmailTemplate("Booking Invoice - SmartPark", "✅ Booking Confirmed", $innerBody);
        return self::sendHTML($to, $subject, $html);
    }
}
?>
