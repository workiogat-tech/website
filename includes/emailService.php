<?php
/**
 * Email Service using SMTP
 * For cPanel email configuration
 */

require_once __DIR__ . '/../config/config.php';

class EmailService {
    private $smtpHost;
    private $smtpPort;
    private $smtpSecure;
    private $smtpUser;
    private $smtpPass;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpSecure = SMTP_SECURE;
        $this->smtpUser = SMTP_USER;
        $this->smtpPass = SMTP_PASS;
        $this->fromEmail = SMTP_FROM_EMAIL ?: SMTP_USER;
        $this->fromName = SMTP_FROM_NAME;
    }
    
    /**
     * Send email using PHP mail() function with SMTP headers
     * For cPanel, you can use PHPMailer library or native mail()
     */
    public function sendEmail($to, $subject, $html, $text = '') {
        // If SMTP is not configured, use PHP mail()
        if (empty($this->smtpUser) || empty($this->smtpPass)) {
            return $this->sendViaPHPMail($to, $subject, $html, $text);
        }
        
        // Use PHPMailer if available, otherwise use mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendViaPHPMailer($to, $subject, $html, $text);
        } else {
            return $this->sendViaPHPMail($to, $subject, $html, $text);
        }
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendViaPHPMail($to, $subject, $html, $text) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $message = $html;
        if (empty($html)) {
            $message = $text;
            $headers[1] = 'Content-Type: text/plain; charset=UTF-8';
        }
        
        $result = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if ($result) {
            error_log("Email sent successfully to: $to");
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            error_log("Failed to send email to: $to");
            return ['success' => false, 'error' => 'Failed to send email'];
        }
    }
    
    /**
     * Send email using PHPMailer (if installed)
     */
    private function sendViaPHPMailer($to, $subject, $html, $text) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPass;
            $mail->SMTPSecure = $this->smtpSecure ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->addReplyTo($this->fromEmail, $this->fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $text ?: strip_tags($html);
            
            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return ['success' => false, 'error' => $mail->ErrorInfo];
        }
    }
    
    /**
     * Send contact form email
     */
    public function sendContactFormEmail($data, $contactEmail) {
        $html = $this->getContactFormEmailTemplate($data);
        $text = $this->getContactFormEmailText($data);
        
        $subject = 'New Contact Form Submission';
        if (!empty($data['subject'])) {
            $subject .= ' - ' . $data['subject'];
        }
        $subject .= ' - ' . ($data['name'] ?? 'Website Visitor');
        
        $results = [];
        
        // Send to contact email if provided
        if (!empty($contactEmail)) {
            $results[] = $this->sendEmail($contactEmail, $subject, $html, $text);
        }
        
        // Send to admin emails from .env
        $adminEmails = ADMIN_EMAILS;
        if (!empty($adminEmails)) {
            $emails = array_map('trim', explode(',', $adminEmails));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $results[] = $this->sendEmail($email, $subject, $html, $text);
                }
            }
        }
        
        if (empty($results)) {
            return ['success' => false, 'message' => 'No recipient emails configured'];
        }
        
        return ['success' => true, 'message' => 'Emails sent to ' . count($results) . ' recipient(s)'];
    }
    
    /**
     * Send sponsorship application email
     */
    public function sendSponsorshipApplicationEmail($data, $coordinatorEmail) {
        $html = $this->getSponsorshipApplicationEmailTemplate($data);
        $text = $this->getSponsorshipApplicationEmailText($data);
        
        $subject = 'New Sponsorship Application - ' . ($data['name'] ?? 'Website Submission');
        
        $results = [];
        
        // Send to coordinator email if provided
        if (!empty($coordinatorEmail)) {
            $results[] = $this->sendEmail($coordinatorEmail, $subject, $html, $text);
        }
        
        // Send to admin emails from .env
        $adminEmails = ADMIN_EMAILS;
        if (!empty($adminEmails)) {
            $emails = array_map('trim', explode(',', $adminEmails));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $results[] = $this->sendEmail($email, $subject, $html, $text);
                }
            }
        }
        
        if (empty($results)) {
            return ['success' => false, 'message' => 'No recipient emails configured'];
        }
        
        return ['success' => true, 'message' => 'Emails sent to ' . count($results) . ' recipient(s)'];
    }
    
    /**
     * Send child sponsorship email
     */
    public function sendChildSponsorshipEmail($data, $coordinatorEmail) {
        $html = $this->getChildSponsorshipEmailTemplate($data);
        $text = $this->getChildSponsorshipEmailText($data);
        
        $subject = 'New Child Sponsorship Request - ' . ($data['sponsor_name'] ?? 'Website Submission');
        
        $results = [];
        
        // Send to coordinator email if provided
        if (!empty($coordinatorEmail)) {
            $results[] = $this->sendEmail($coordinatorEmail, $subject, $html, $text);
        }
        
        // Send to admin emails from .env
        $adminEmails = ADMIN_EMAILS;
        if (!empty($adminEmails)) {
            $emails = array_map('trim', explode(',', $adminEmails));
            foreach ($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $results[] = $this->sendEmail($email, $subject, $html, $text);
                }
            }
        }
        
        if (empty($results)) {
            return ['success' => false, 'message' => 'No recipient emails configured'];
        }
        
        return ['success' => true, 'message' => 'Emails sent to ' . count($results) . ' recipient(s)'];
    }
    
    // Email templates
    private function getContactFormEmailTemplate($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #800020; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #800020; }
                .value { margin-top: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <h2>New Contact Form Submission</h2>
                </div>
                <div class=\"content\">
                    <p>A new contact form submission has been received through the website.</p>
                    
                    <div class=\"field\">
                        <div class=\"label\">Name:</div>
                        <div class=\"value\">" . htmlspecialchars($data['name'] ?? 'N/A') . "</div>
                    </div>
                    
                    <div class=\"field\">
                        <div class=\"label\">Email:</div>
                        <div class=\"value\">" . htmlspecialchars($data['email'] ?? 'N/A') . "</div>
                    </div>
                    
                    " . (!empty($data['phone']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Phone:</div>
                        <div class=\"value\">" . htmlspecialchars($data['phone']) . "</div>
                    </div>
                    " : "") . "
                    
                    " . (!empty($data['subject']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Subject:</div>
                        <div class=\"value\">" . htmlspecialchars($data['subject']) . "</div>
                    </div>
                    " : "") . "
                    
                    " . (!empty($data['message']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Message:</div>
                        <div class=\"value\">" . nl2br(htmlspecialchars($data['message'])) . "</div>
                    </div>
                    " : "") . "
                    
                    <div class=\"field\">
                        <div class=\"label\">Submitted At:</div>
                        <div class=\"value\">$submittedAt</div>
                    </div>
                </div>
                <div class=\"footer\">
                    <p>This is an automated notification from the school website.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getContactFormEmailText($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "New Contact Form Submission

A new contact form submission has been received through the website.

Name: " . ($data['name'] ?? 'N/A') . "
Email: " . ($data['email'] ?? 'N/A') . "
" . (!empty($data['phone']) ? "Phone: " . $data['phone'] . "\n" : "") . "
" . (!empty($data['subject']) ? "Subject: " . $data['subject'] . "\n" : "") . "
" . (!empty($data['message']) ? "Message: " . $data['message'] . "\n" : "") . "
Submitted At: $submittedAt";
    }
    
    private function getSponsorshipApplicationEmailTemplate($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #800020; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #800020; }
                .value { margin-top: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <h2>New Sponsorship Application</h2>
                </div>
                <div class=\"content\">
                    <p>A new sponsorship application has been submitted through the website.</p>
                    
                    <div class=\"field\">
                        <div class=\"label\">Name:</div>
                        <div class=\"value\">" . htmlspecialchars($data['name'] ?? 'N/A') . "</div>
                    </div>
                    
                    <div class=\"field\">
                        <div class=\"label\">Email:</div>
                        <div class=\"value\">" . htmlspecialchars($data['email'] ?? 'N/A') . "</div>
                    </div>
                    
                    " . (!empty($data['phone']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Phone:</div>
                        <div class=\"value\">" . htmlspecialchars($data['phone']) . "</div>
                    </div>
                    " : "") . "
                    
                    <div class=\"field\">
                        <div class=\"label\">Sponsorship Level:</div>
                        <div class=\"value\">" . htmlspecialchars($data['sponsorship_level'] ?? 'N/A') . "</div>
                    </div>
                    
                    " . (!empty($data['message']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Message:</div>
                        <div class=\"value\">" . nl2br(htmlspecialchars($data['message'])) . "</div>
                    </div>
                    " : "") . "
                    
                    <div class=\"field\">
                        <div class=\"label\">Submitted At:</div>
                        <div class=\"value\">$submittedAt</div>
                    </div>
                </div>
                <div class=\"footer\">
                    <p>This is an automated notification from the school website.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getSponsorshipApplicationEmailText($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "New Sponsorship Application

A new sponsorship application has been submitted through the website.

Name: " . ($data['name'] ?? 'N/A') . "
Email: " . ($data['email'] ?? 'N/A') . "
" . (!empty($data['phone']) ? "Phone: " . $data['phone'] . "\n" : "") . "
Sponsorship Level: " . ($data['sponsorship_level'] ?? 'N/A') . "
" . (!empty($data['message']) ? "Message: " . $data['message'] . "\n" : "") . "
Submitted At: $submittedAt";
    }
    
    private function getChildSponsorshipEmailTemplate($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #800020; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #800020; }
                .value { margin-top: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <div class=\"header\">
                    <h2>New Child Sponsorship Request</h2>
                </div>
                <div class=\"content\">
                    <p>A new child sponsorship request has been submitted through the website.</p>
                    
                    <div class=\"field\">
                        <div class=\"label\">Sponsor Name:</div>
                        <div class=\"value\">" . htmlspecialchars($data['sponsor_name'] ?? 'N/A') . "</div>
                    </div>
                    
                    <div class=\"field\">
                        <div class=\"label\">Sponsor Email:</div>
                        <div class=\"value\">" . htmlspecialchars($data['sponsor_email'] ?? 'N/A') . "</div>
                    </div>
                    
                    " . (!empty($data['sponsor_phone']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Sponsor Phone:</div>
                        <div class=\"value\">" . htmlspecialchars($data['sponsor_phone']) . "</div>
                    </div>
                    " : "") . "
                    
                    " . (!empty($data['child_name']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Child Name:</div>
                        <div class=\"value\">" . htmlspecialchars($data['child_name']) . "</div>
                    </div>
                    " : "") . "
                    
                    <div class=\"field\">
                        <div class=\"label\">Sponsorship Type:</div>
                        <div class=\"value\">" . htmlspecialchars($data['sponsorship_type'] ?? 'N/A') . "</div>
                    </div>
                    
                    " . (!empty($data['amount']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Amount:</div>
                        <div class=\"value\">" . htmlspecialchars($data['amount']) . " UGX</div>
                    </div>
                    " : "") . "
                    
                    " . (!empty($data['message']) ? "
                    <div class=\"field\">
                        <div class=\"label\">Message:</div>
                        <div class=\"value\">" . nl2br(htmlspecialchars($data['message'])) . "</div>
                    </div>
                    " : "") . "
                    
                    <div class=\"field\">
                        <div class=\"label\">Submitted At:</div>
                        <div class=\"value\">$submittedAt</div>
                    </div>
                </div>
                <div class=\"footer\">
                    <p>This is an automated notification from the school website.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getChildSponsorshipEmailText($data) {
        $submittedAt = isset($data['submittedAt']) ? date('F j, Y, g:i a', strtotime($data['submittedAt'])) : date('F j, Y, g:i a');
        
        return "New Child Sponsorship Request

A new child sponsorship request has been submitted through the website.

Sponsor Name: " . ($data['sponsor_name'] ?? 'N/A') . "
Sponsor Email: " . ($data['sponsor_email'] ?? 'N/A') . "
" . (!empty($data['sponsor_phone']) ? "Sponsor Phone: " . $data['sponsor_phone'] . "\n" : "") . "
" . (!empty($data['child_name']) ? "Child Name: " . $data['child_name'] . "\n" : "") . "
Sponsorship Type: " . ($data['sponsorship_type'] ?? 'N/A') . "
" . (!empty($data['amount']) ? "Amount: " . $data['amount'] . " UGX\n" : "") . "
" . (!empty($data['message']) ? "Message: " . $data['message'] . "\n" : "") . "
Submitted At: $submittedAt";
    }
}

// Helper function
function getEmailService() {
    return new EmailService();
}
