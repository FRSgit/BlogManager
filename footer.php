<div id="chat">
	<div id="chatToggleOut">
		<label id="chatToggleBtnIn" for="toggleChat">
			<span></span>
			<input type="checkbox" id="toggleChat" name="toggleChat" />
			Toggle chat
		</label>
	</div>
	<div class="clear"></div>
	<div id="chatHide">
		<div id="chatCont">
			<div id="msgsContOut">
				<textarea id="msgsCont" readonly rows="15" cols="100">Loading messages...</textarea>
			</div>
			<div id="chatLeft">
				<input id="chatName" name="chatName" size="<?php echo $CHAT_MAXLENGTH_AUTHOR+1; ?>" placeholder="Type in your name" maxlength="<?php echo $CHAT_MAXLENGTH_AUTHOR; ?>" id="chatName" type="text" />
			</div>
			<div id="chatRight">
				Hit <strong>enter</strong> or <button id="chatSend" class="disabled" type="button" disabled><strong>click</strong> to send</button>
			</div>
			<div id="chatCenter">
				<input id="chatMsg" name="chatMsg" placeholder="Type in message" autocomplete="off" maxlength="<?php echo $CHAT_MAXLENGTH_MSG; ?>" type="text" disabled />
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>