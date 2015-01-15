<div id="chat">
	<div id="chatToggleOut">
		<label id="chatToggleBtnIn" for="toggleChat">
			<span></span>
			<input type="checkbox" id="toggleChat" name="toggleChat" autocomplete="off" />
			Toggle chat
		</label>
	</div>
	<div class="clear"></div>
	<div id="chatHide">
		<div id="chatCont">
			<div id="msgsContOut">
				<textarea id="msgsCont" readonly="readonly" rows="15" cols="100">Loading messages...</textarea>
			</div>
			<div id="chatLeft">
				<input id="chatName" name="chatName" tabindex="1" size="<?php echo $GLOBALS["CHAT_MAXLENGTH_AUTHOR"]+1; ?>" placeholder="Type in your name" maxlength="<?php echo $GLOBALS["CHAT_MAXLENGTH_AUTHOR"]; ?>" type="text" disabled="disabled" />
			</div>
			<div id="chatRight">
				Hit <strong>enter</strong> or <button id="chatSend" class="disabled" type="button" tabindex="3" disabled="disabled"><strong>click</strong> to send</button>
			</div>
			<div id="chatCenter">
				<input id="chatMsg" name="chatMsg" tabindex="2" placeholder="Type in message" autocomplete="off" maxlength="<?php echo $GLOBALS["CHAT_MAXLENGTH_MSG"]; ?>" type="text" disabled="disabled" />
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>