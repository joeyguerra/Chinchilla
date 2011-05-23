<?php foreach($members as $member):?>
	<article>
		<header>
			<h2>
				<a href="<?php echo App::url_for($member->is_owner ? null : $member->signin);?>" title="Visit <?php echo $member->name;?>'s site">
					<?php echo $member->display_name;?>
				</a>
			</h2>
		</header>
		<footer>
	<?php if(AuthController::is_authed() && AuthController::$current_user->is_owner):?>
			<a href="<?php echo App::url_for("member", array("id"=>$member->id));?>">edit</a>
	<?php endif;?>
		</footer>
	</article>
<?php endforeach;?>
<?php if(AuthController::is_authed() && AuthController::$current_user->is_owner):?>
<a href="<?php echo App::url_for("member");?>">add a member</a>
<?php endif;?>