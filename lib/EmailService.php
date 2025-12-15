<?php
namespace ProjetHiba\Lib;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;

    public function __construct(
        string $smtpHost,
        string $smtpUser,
        string $smtpPass,
        int $smtpPort = 587,
        ?string $fromEmail = null,
        string $fromName = 'SOLIDA - Support Réclamations'
    ) {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $smtpHost;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $smtpUser;
        $this->mailer->Password = $smtpPass;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $smtpPort;
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom($fromEmail ?: $smtpUser, $fromName);
    }

    public function sendComplaintReply(
        string $toEmail,
        string $toName,
        int $complaintId,
        string $description,
        string $replyDate,
        string $newStatus
    ): bool {
        $subject = "Réponse à votre réclamation #{$complaintId}";

        $html = <<<HTML
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>{$subject}</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f6f8; margin:0; padding:20px; }
    .card { max-width: 760px; margin:auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.06); overflow:hidden; }
    .header { background:#60b168; color:#fff; padding:24px; display:flex; align-items:center; }
    .header-title { font-size:24px; font-weight:700; margin-left:12px; }
    .content { padding:24px; color:#333; }
    .section-title { font-size:22px; margin:0 0 16px; }
    .label { color:#567; font-weight:600; }
    .value { background:#f1f4f6; padding:10px 12px; border-radius:6px; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <span style="font-size:28px;">📝</span>
      <div class="header-title">SOLIDA</div>
    </div>
    <div class="content">
      <h2 class="section-title">Bonjour {$toName},</h2>
      <p>Nous vous informons qu'une réponse a été apportée à votre réclamation <strong>#{$complaintId}</strong>.</p>

      <h3>Détails de votre réclamation</h3>
      <p class="label">Numéro:</p>
      <div class="value">#{$complaintId}</div>
      <p class="label">Description:</p>
      <div class="value">{$description}</div>
      <p class="label">Date réponse:</p>
      <div class="value">{$replyDate}</div>
      <p class="label">Nouveau statut:</p>
      <div class="value">{$newStatus}</div>

      <p style="margin-top:24px">Centre de support réclamations SOLIDA</p>
    </div>
  </div>
</body>
</html>
HTML;

        $plain = "Bonjour {$toName},\n\n".
                 "Une réponse a été apportée à votre réclamation #{$complaintId}.\n\n".
                 "Description: {$description}\n".
                 "Date réponse: {$replyDate}\n".
                 "Nouveau statut: {$newStatus}\n\n".
                 "Centre de support réclamations SOLIDA";

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $html;
            $this->mailer->AltBody = $plain;
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }
}