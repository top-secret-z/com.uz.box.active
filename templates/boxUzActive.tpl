<ul class="sidebarItemList">
	{foreach from=$boxUserList item=$boxUser}
		<li class="box32">
			<a href="{link controller='User' object=$boxUser}{/link}" aria-hidden="true">{@$boxUser->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarItemTitle">
				<h3><a href="{link controller='User' object=$boxUser}{/link}" class="userLink" data-user-id="{@$boxUser->userID}">{$boxUser->username}</a></h3>
				<small>{lang}wcf.user.uzboxActive.activityPoints{/lang}</small>
			</div>
		</li>
	{/foreach}
</ul>
