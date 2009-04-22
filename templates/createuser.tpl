{START_FORM}

<div class="padded">{INSTRUCTIONS}</div>
<div class="padded">
    <strong>{OPENID_LABEL}</strong><br />
    <img src="images/mod/openid/openid_small_logo.png" alt="OpenID" title="OpenID" /> {OPENID}
</div>
<div class="padded">
    <strong>{USERNAME_LABEL}</strong><br />{USERNAME}
    <!-- BEGIN username_error --><div class="error">{USERNAME_ERROR}</div><!-- END username_error -->
</div>
<div class="padded">
    <strong>{DISPLAYNAME_LABEL}</strong><br />{DISPLAYNAME}
    <!-- BEGIN displayname_error --><div class="error">{DISPLAYNAME_ERROR}</div><!-- END displayname_error -->
</div>
<div class="padded">
    <strong>{EMAIL_LABEL}</strong><br />{EMAIL}
    <!-- BEGIN email_error --><div class="error">{EMAIL_ERROR}</div><!-- END email_error -->
</div>
<div class="padded">{SUBMIT}</div>

{END_FORM}