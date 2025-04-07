<?php
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;
    private $mailer;

    public function __construct() {
        $this->config = [
            'smtp' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'ufodisclosurebulgaria.website@gmail.com',
                'password' => 'kpsccwmiuvhkwvef',
                'encryption' => 'tls',
                'from_email' => 'ufodisclosurebulgaria.website@gmail.com',
                'from_name' => 'UFO Disclosure Bulgaria'
            ]
        ];
        $this->initializeMailer();
    }

    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp']['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['smtp']['username'];
        $this->mailer->Password = $this->config['smtp']['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['smtp']['port'];
        $this->mailer->CharSet = 'UTF-8';
        
        $this->mailer->setFrom(
            $this->config['smtp']['from_email'],
            $this->config['smtp']['from_name']
        );
    }

    public function sendVerificationEmail($toEmail, $token, $language = 'en') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $language === 'bg' ? 
                'Потвърдете вашия имейл адрес' : 
                'Verify Your Email Address';
            
             $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . 
                               "/UfoDisclosureBulgaria/classes/verify.php?token=" . $token . 
                               "&lang=" . $language;
            //$verificationLink = "http://" . $_SERVER['HTTP_HOST'] . 
               // "/classes/verify.php?token=" . $token . 
               // "&lang=" . $language;
            
            $this->mailer->Body = $this->getEmailTemplate($verificationLink, $language);
            $this->mailer->AltBody = $language === 'bg' ? 
                'Моля, потвърдете имейла си, като посетите: ' . $verificationLink :
                'Please verify your email by visiting: ' . $verificationLink;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    private function getEmailTemplate($verificationLink, $language) {
        if ($language === 'bg') {
            return $this->getBulgarianTemplate($verificationLink);
        }
        return $this->getEnglishTemplate($verificationLink);
    }

    private function getEnglishTemplate($verificationLink) {
        return "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            </head>
            <body>
                <div style='font-family: Arial, sans-serif; max-width: 700px; background-color: #1a1a1a; color: #ffffff; padding: 30px; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #ffffff; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: 2px;'>
                            <span style='color: #744769;'>UFO</span> DISCLOSURE BULGARIA
                        </h1>
                    </div>

                    <div style='background-color: rgba(116, 71, 105, 0.1); padding: 25px; border-radius: 8px; border: 1px solid #744769; margin-bottom: 20px;'>
                        <h2 style='color: #744769; margin-bottom: 20px; text-align: center;'>WELCOME TO OUR COMMUNITY</h2>
                        
                        <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                            Thank you for joining UFO Disclosure Bulgaria!</p>
						<p	style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
							To begin exploring the truth that's out there, please verify your email address.</p>                      

                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$verificationLink}' 
                               style='background-color: #744769; 
                                      color: white; 
                                      padding: 15px 30px; 
                                      text-decoration: none; 
                                      border-radius: 25px;
                                      display: inline-block;
                                      font-weight: bold;
                                      text-transform: uppercase;
                                      letter-spacing: 1px;
                                      box-shadow: 0 4px 8px rgba(116, 71, 105, 0.3);'>
                                Verify Your Email
                            </a>
                        </div>
                    </div>

                    <div style='margin-top: 30px; text-align: center; color: #888888; font-size: 12px;'>
                        <p style='margin-bottom: 10px;'>
                            This verification link will expire in 24 hours.
                        </p>
                        <p style='border-top: 1px solid #744769; padding-top: 15px; margin-top: 15px;'>
                            © " . date('Y') . " UFO Disclosure Bulgaria. All rights reserved.
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    private function getBulgarianTemplate($verificationLink) {
        return "
            <!DOCTYPE html>
            <html lang='bg'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            </head>
            <body>
                <div style='font-family: Arial, sans-serif; max-width: 700px; background-color: #1a1a1a; color: #ffffff; padding: 30px; border-radius: 10px;'>
                    <div style='text-align: center; margin-bottom: 30px;'>
                        <h1 style='color: #ffffff; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: 2px;'>
                            <span style='color: #744769;'>UFO</span> DISCLOSURE BULGARIA
                        </h1>
                    </div>

                    <div style='background-color: rgba(116, 71, 105, 0.1); padding: 25px; border-radius: 8px; border: 1px solid #744769; margin-bottom: 20px;'>
                        <h2 style='color: #744769; margin-bottom: 20px; text-align: center;'>ДОБРЕ ДОШЛИ В НАШАТА ОБЩНОСТ</h2>
                        
                        <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                            Благодарим ви, че се присъединихте към UFO Disclosure Bulgaria!</p>
						<p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>	
							За да започнете да изследвате истината, моля, потвърдете вашия имейл адрес.</p>              

                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$verificationLink}' 
                               style='background-color: #744769; 
                                      color: white; 
                                      padding: 15px 30px; 
                                      text-decoration: none; 
                                      border-radius: 25px;
                                      display: inline-block;
                                      font-weight: bold;
                                      text-transform: uppercase;
                                      letter-spacing: 1px;
                                      box-shadow: 0 4px 8px rgba(116, 71, 105, 0.3);'>
                                Потвърдете вашия имейл
                            </a>
                        </div>
                    </div>

                    <div style='margin-top: 30px; text-align: center; color: #888888; font-size: 12px;'>
                        <p style='margin-bottom: 10px;'>
                            Този линк за потвърждение ще изтече след 24 часа.
                        </p>
                        <p style='border-top: 1px solid #744769; padding-top: 15px; margin-top: 15px;'>
                            © " . date('Y') . " UFO Disclosure Bulgaria. Всички права запазени.
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }


public function sendPasswordResetEmail($toEmail, $token, $language = 'en') {
    try {
        $this->mailer->clearAddresses();
        $this->mailer->addAddress($toEmail);
        
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $language === 'bg' ? 
            'Възстановяване на парола - UFO Disclosure Bulgaria' : 
            'Password Reset - UFO Disclosure Bulgaria';
        
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . 
                    "/UfoDisclosureBulgaria/login_register/forgotten_password.php?token=" . $token . 
                    "&lang=" . $language;
        
        $this->mailer->Body = $this->getPasswordResetTemplate($resetLink, $language);
        $this->mailer->AltBody = $language === 'bg' ? 
            'Моля, следвайте този линк, за да възстановите паролата си: ' . $resetLink :
            'Please follow this link to reset your password: ' . $resetLink;
        
        return $this->mailer->send();
    } catch (Exception $e) {
        error_log("Password reset email sending failed: {$this->mailer->ErrorInfo}");
        return false;
    }
}

private function getPasswordResetTemplate($resetLink, $language) {
    if ($language === 'bg') {
        return $this->getBulgarianPasswordResetTemplate($resetLink);
    }
    return $this->getEnglishPasswordResetTemplate($resetLink);
}

private function getEnglishPasswordResetTemplate($resetLink) {
    return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 700px; margin: left; background-color: #1a1a1a; color: #ffffff; padding: 30px; border-radius: 10px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #ffffff; font-family: Arial, sans-serif; text-align: center; text-transform: uppercase; letter-spacing: 2px;'>
                        <span style='color: #744769;'>UFO</span> DISCLOSURE BULGARIA
                    </h1>
                </div>

                <div style='background-color: rgba(116, 71, 105, 0.1); padding: 25px; border-radius: 8px; border: 1px solid #744769; margin-bottom: 20px;'>
                    <h2 style='color: #744769; margin-bottom: 20px; text-align: center;'>Password Reset Request</h2>
                    
                    <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                        We received a request to reset your password for your UFO Disclosure Bulgaria account.</p>
                    <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                        If you didn't make this request, you can safely ignore this email.</p>                      

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' 
                           style='background-color: #744769; 
                                  color: white; 
                                  padding: 15px 30px; 
                                  text-decoration: none; 
                                  border-radius: 25px;
                                  display: inline-block;
                                  font-weight: bold;
                                  text-transform: uppercase;
                                  letter-spacing: 1px;
                                  box-shadow: 0 4px 8px rgba(116, 71, 105, 0.3);'>
                            Reset Your Password
                        </a>
                    </div>
                </div>

                <div style='margin-top: 30px; text-align: center; color: #888888; font-size: 12px;'>
                    <p style='margin-bottom: 10px;'>
                        This password reset link will expire in 24 hours.
                    </p>
                    <p style='border-top: 1px solid #744769; padding-top: 15px; margin-top: 15px;'>
                        © " . date('Y') . " UFO Disclosure Bulgaria. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
    ";
}

private function getBulgarianPasswordResetTemplate($resetLink) {
    return "
        <!DOCTYPE html>
        <html lang='bg'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 700px; margin: left; background-color: #1a1a1a; color: #ffffff; padding: 30px; border-radius: 10px;'>
                <div style='text-align: left; margin-bottom: 30px;'>
                    <h1 style='color: #ffffff; font-family: Arial, sans-serif; text-transform: uppercase; text-align: center; letter-spacing: 2px;'>
                        <span style='color: #744769;'>UFO</span> DISCLOSURE BULGARIA
                    </h1>
                </div>

                <div style='background-color: rgba(116, 71, 105, 0.1); padding: 25px; border-radius: 8px; border: 1px solid #744769; margin-bottom: 20px;'>
                    <h2 style='color: #744769; margin-bottom: 20px; text-align: center;'>Заявка за възстановяване на парола</h2>
                    
                    <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                        Получихме заявка за възстановяване на паролата за вашия акаунт в UFO Disclosure Bulgaria.</p>
                    <p style='color: #ffffff; line-height: 1.6; margin-bottom: 25px; text-align: center;'>
                        Ако не сте направили тази заявка, можете спокойно да игнорирате този имейл.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' 
                           style='background-color: #744769; 
                                  color: white; 
                                  padding: 15px 30px; 
                                  text-decoration: none; 
                                  border-radius: 25px;
                                  display: inline-block;
                                  font-weight: bold;
                                  text-transform: uppercase;
                                  letter-spacing: 1px;
                                  box-shadow: 0 4px 8px rgba(116, 71, 105, 0.3);'>
                            Възстановете вашата парола
                        </a>
                    </div>
                </div>

                <div style='margin-top: 30px; text-align: center; color: #888888; font-size: 12px;'>
                    <p style='margin-bottom: 10px;'>
                        Този линк за възстановяване на паролата ще изтече след 24 часа.
                    </p>
                    <p style='border-top: 1px solid #744769; padding-top: 15px; margin-top: 15px;'>
                        © " . date('Y') . " UFO Disclosure Bulgaria. Всички права запазени.
                    </p>
                </div>
            </div>
        </body>
        </html>
    ";
}
}