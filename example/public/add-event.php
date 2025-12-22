<?php

declare(strict_types=1);

use Channel\Client;

require_once __DIR__.'/../vendor/autoload.php';

session_start();

// Handle form
$formErrors = [];

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $userId = (string) ($_POST['userId'] ?? '');
    $data = (string) ($_POST['data'] ?? '');

    if ('' === $userId) {
        $formErrors[] = 'User ID is empty';
    }

    if ([] !== $formErrors) {
        goto finishForm;
    }

    //Client::connect('unix:///var/www/html/var/run/sse-server.sock');
    Client::connect('sse');
    Client::publish(
        'events', // Value of env var CHANNEL, using default channel name "events"
        [
            'event' => 'notification', // Event name
            'data' => $data, // Sent data
            'userId' => $userId, // Recipient User ID
        ],
    );

    header('Location: /add-event.php');
    $_SESSION['form-success'] = true;
    $_SESSION['userId'] = $userId;
    return;
}

finishForm:

$formSuccess = (bool) ($_SESSION['form-success'] ?? false);
unset($_SESSION['form-success']);
$userId = (string) ($_SESSION['userId'] ?? 'sse');
unset($_SESSION['userId']);

?>

<html>
    <body>
        <h1>Send notification</h1>
        <div>
            <?php if ($formSuccess): ?>
                <p style="color: green;">Notification success sent</p>
            <?php endif; ?>

            <?php if ([] !== $formErrors): ?>
                <p><b>Errors:</b></p>
                <?php foreach ($formErrors as $formError): ?>
                    <p style="color: red;"><?php echo $formError; ?></p>
                <?php endforeach ?>
                <p></p>
            <?php endif ?>

            <form action="/add-event.php" method="post">
                <div>
                    <label>
                        Recipient User ID:
                        <br>
                        <input type="text" name="userId" value="<?php echo $userId; ?>" required />
                    </label>
                </div>
                <div>
                    <label>
                        Data:
                        <br>
                        <textarea name="data" cols="30" rows="10"></textarea>
                    </label>
                </div>
                <div>
                    <br>
                    <input type="submit" value="Send">
                </div>
            </form>
        </div>
    </body>
</html>
