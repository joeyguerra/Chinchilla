<ul>
<?php foreach($errors as $key=>$message):?>
	<li class="<?php echo $key;?>"><?php echo $message;?></li>
<?php endforeach;?>
</ul>