{include file='header.tpl' section='login'}

<form method="post" action="/account/login">
    <fieldset>
        <input type="hidden" name="redirect" value="{$redirect|escape}" />

        <legend>Log In to Your Account</legend>

        <div class="row" id="form_username_container">
            <label for="form_username">Username:</label>
            <input type="text" id="form_username"
                   name="username" value="{$username|escape}" />
            {include file='lib/error.tpl' error=$errors.username}
        </div>

        <div class="row" id="form_password_container">
            <label for="form_password">Password:</label>
            <input type="password" id="form_password"
                   name="password" value="" />
            {include file='lib/error.tpl' error=$errors.password}
        </div>

		<div class="row">
			<label for="form_expires">Expires:</label><br />
			<input type="radio" name="expires" value="31536000" />Ever
			<input type="radio" name="expires" value="2592000" />Month
			<input type="radio" name="expires" value="86400" />Day
			<input type="radio" name="expires" value="3600" />Hour
			<input type="radio" name="expires" value="0" checked />No
		</div>

        <div class="submit">
            <input type="submit" value="Login" />
        </div>

        <div>
            <a href="/account/fetchpassword">Forgotten your password?</a>
        </div>
    </fieldset>
</form>

{include file='footer.tpl'}