<form name="form1" method="post" action="">
    <div><?php echo $question->question; ?></div>
    <hr />
    <?php foreach($answers as $answer) { ?>
        <div><input type="radio" id="answer-<?php echo $answer->id; ?>" name="answer" value="<?php echo $answer->id; ?>"> <label for="answer-<?php echo $answer->id; ?>"><?php echo $answer->answer; ?></label></div>
    <?php } ?>
    <div>
    <br />
    <div><input type="submit" value="Next" /></div>
    <?php if($hasPrevious) { ?>
        <div><a href="<?php echo add_query_arg( 'page', get_query_var('page', 1)-1 ); ?>">Previous question</a></div>
    <?php } ?>
    </div>
</form>
<?php return; ?>