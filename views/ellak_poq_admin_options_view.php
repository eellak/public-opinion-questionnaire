<div class="wrap">
    <h2><?php _e('Public Opinion Questionnaire', 'public-opinion-questionnaire' ); ?></h2>
    <h4><?php _e('Import questions from CSV file', 'public-opinion-questionnaire' ); ?></h4>
    <form name="form1" method="post" action="">
        <input type="file" name="import" />
        <?php submit_button(); ?>
    </form>

    <h4><?php _e('Preload answers', 'public-opinion-questionnaire' ); ?></h4>
    <form name="form1" method="post" action="">
        <input type="file" name="import" />
        <?php submit_button(); ?>
    </form>
</div>