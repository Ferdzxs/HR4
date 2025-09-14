<?php
// HR4 Password Reset Request
session_start();
include_once 'config/auth.php';

$message = '';
$error = '';

// Handle password reset request
if ($_POST['reset_request'] ?? false) {
    $email = trim($_POST['email'] ?? '');
    
    if (!empty($email)) {
        // In a real application, you would:
        // 1. Validate email format
        // 2. Check if email exists in database
        // 3. Generate secure reset token
        // 4. Store token in database with expiration
        // 5. Send email with reset link
        
        // For now, just show a message
        $message = 'If an account with that email exists, a password reset link has been sent.';
    } else {
        $error = 'Please enter your email address.';
    }
}

// If already logged in, redirect to app
if (isset($_SESSION['user'])) {
    header('Location: app.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Password Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include 'shared/styles.php'; ?>
</head>
<body>
    <div class="h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
        <div class="h-full flex items-center justify-center p-6">
            <div class="w-full max-w-md space-y-6 animate-fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center gap-3 text-brand-600 dark:text-brand-400 mb-2">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white font-bold text-lg shadow-lg">H4</span>
                        <div class="text-2xl font-bold">HR4</div>
                    </div>
                    <div class="text-sm text-slate-500">Compensation & HR Intelligence</div>
                    <div class="text-lg font-semibold mt-4">Reset Password</div>
                    <p class="text-sm text-slate-500 mt-2">Enter your email address and we'll send you a link to reset your password.</p>
                </div>
                
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                    <div class="p-6">
                        <?php if ($error): ?>
                            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium mb-2">Email Address</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-3 py-2 border border-[hsl(var(--input))] rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 transition-colors"
                                       placeholder="Enter your email address"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       autocomplete="email">
                            </div>
                            
                            <button type="submit" name="reset_request" value="1"
                                    class="w-full bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] py-2 px-4 rounded-md hover:opacity-95 transition-colors focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2">
                                Send Reset Link
                            </button>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <a href="login.php" class="text-sm text-[hsl(var(--primary))] hover:underline">
                                ← Back to Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center text-xs text-slate-500">
                    <div class="mb-2">Need help?</div>
                    <div>Contact your system administrator for assistance</div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'shared/scripts.php'; ?>
</body>
</html>
