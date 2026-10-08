<?php
$currentUser = wp_get_current_user();
?>

<div class="head">
    <div class="title"><span>1</span> Connexion</div>
    <?php if (!is_user_logged_in()) : ?>
        <div class="toggle">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/resources/images/icon-chevron-checkout.svg" alt="Flèche toggle" width="24" height="10">
        </div>
    <?php endif; ?>
</div>

<div>
    <?php if (is_user_logged_in()) : ?>
        <div class="loggedUser">
            <p><?= sprintf("%s - %s %s", $currentUser->user_email, $currentUser->first_name, $currentUser->last_name); ?></p>
            <a href="<?= wc_logout_url(); ?>">Déconnexion</a>
        </div>
    <?php endif; ?>
</div>

<div class="step-content step-login">
    <?php if (!is_user_logged_in()) : ?>
        <div class="toggle-login-form showLoginForm">
            <span class="login-account">Se connecter</span>
            <div>
                <span class="noAccountYet">Pas encore de compte ?</span>
                <span class="create-account">Créer un compte</span>
            </div>
        </div>
        <?php include(locate_template('woocommerce/myaccount/form-login.php')); ?>
    <?php endif; ?>
</div>
