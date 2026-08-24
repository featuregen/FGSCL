<?php
/**
 * Email Service
 * Handles sending emails via SMTP
 * Uses PHPMailer if available, falls back to PHP mail()
 */

class EmailService
{
    private array $config;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/mail.php';
    }

    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        // Try PHPMailer first (if composer installed)
        $phpmailerPath = ROOT_PATH . '/vendor/autoload.php';
        if (file_exists($phpmailerPath)) {
            return $this->sendWithPHPMailer($to, $subject, $body, $attachments);
        }

        // Fallback to PHP mail()
        return $this->sendWithMail($to, $subject, $body);
    }

    /**
     * Send using PHPMailer
     */
    private function sendWithPHPMailer(string $to, string $subject, string $body, array $attachments = []): bool
    {
        require_once ROOT_PATH . '/vendor/autoload.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'] === 'tls' 
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS 
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $this->config['port'];

            // Sender & Recipient
            $mail->setFrom($this->config['from']['address'], $this->config['from']['name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $this->wrapInTemplate($subject, $body);
            $mail->AltBody = strip_tags($body);

            // Attachments
            foreach ($attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }

            $mail->send();
            return true;
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            if (APP_DEBUG) {
                error_log("Email Error: " . $e->getMessage());
            }
            throw new Exception("Failed to send email: " . $e->getMessage());
        }
    }

    /**
     * Send using PHP mail() — fallback
     */
    private function sendWithMail(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['from']['name'] . ' <' . $this->config['from']['address'] . '>',
            'Reply-To: ' . $this->config['from']['address'],
        ];

        $htmlBody = $this->wrapInTemplate($subject, $body);

        if (APP_DEBUG) {
            // In development, log email instead of sending
            $logFile = STORAGE_PATH . '/logs/emails.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logEntry = sprintf(
                "[%s] To: %s | Subject: %s\n%s\n%s\n",
                date('Y-m-d H:i:s'),
                $to,
                $subject,
                strip_tags($body),
                str_repeat('-', 60)
            );
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            
            return true;
        }

        return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    /**
     * Wrap email body in HTML template
     */
    private function wrapInTemplate(string $subject, string $body): string
    {
        $appName = APP_NAME;
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$subject}</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); border-radius: 12px 12px 0 0; padding: 30px; text-align: center;">
                    <h1 style="color: #fff; margin: 0; font-size: 24px; font-weight: 600;">{$appName}</h1>
                </div>
                
                <!-- Body -->
                <div style="background: #fff; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                    {$body}
                </div>
                
                <!-- Footer -->
                <div style="text-align: center; padding: 20px; color: #9CA3AF; font-size: 12px;">
                    <p>&copy; {$year} {$appName}. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Send OTP email
     */
    public function sendOtp(string $to, string $name, string $otp): bool
    {
        $body = "
            <h2 style='color: #1F2937; margin-bottom: 10px;'>Your Verification Code</h2>
            <p style='color: #4B5563;'>Hello {$name},</p>
            <p style='color: #4B5563;'>Your One-Time Password (OTP) is:</p>
            <div style='text-align: center; margin: 25px 0;'>
                <span style='font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #4F46E5; 
                             background: #EEF2FF; padding: 18px 35px; border-radius: 12px; display: inline-block;'>{$otp}</span>
            </div>
            <p style='color: #4B5563;'>This OTP is valid for <strong>10 minutes</strong>.</p>
            <p style='color: #EF4444; font-size: 13px;'>⚠️ Do not share this code with anyone.</p>
        ";

        return $this->send($to, 'Your Verification Code - ' . APP_NAME, $body);
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcome(string $to, string $name, string $password): bool
    {
        $loginUrl = APP_URL . '/auth/login';
        
        $body = "
            <h2 style='color: #1F2937;'>Welcome to " . APP_NAME . "!</h2>
            <p style='color: #4B5563;'>Hello {$name},</p>
            <p style='color: #4B5563;'>Your account has been created. Here are your login credentials:</p>
            <div style='background: #F9FAFB; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                <p style='margin: 5px 0;'><strong>Email:</strong> {$to}</p>
                <p style='margin: 5px 0;'><strong>Password:</strong> {$password}</p>
            </div>
            <p style='color: #4B5563;'>Please change your password after first login.</p>
            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$loginUrl}' style='background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; 
                   text-decoration: none; padding: 12px 35px; border-radius: 8px; font-weight: 600; display: inline-block;'>Login Now</a>
            </div>
        ";

        return $this->send($to, 'Welcome to ' . APP_NAME, $body);
    }
}
