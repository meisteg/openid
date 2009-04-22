{START_FORM}

<fieldset><legend><strong>{DELEGATE} {DELEGATE_LABEL}</strong></legend>
    <p>
        <strong>{DELEGATE_SERVER_LABEL}</strong><br />{DELEGATE_SERVER}<br />
        <span class="smaller">e.g. http://www.myopenid.com/server</span>
    </p>
    <p>
        <strong>{DELEGATE_OPENID_LABEL}</strong><br />{DELEGATE_OPENID}<br />
        <span class="smaller">e.g. http://username.myopenid.com</span>
    </p>
    <p>{DELEGATE_VER_2} {DELEGATE_VER_2_LABEL}</p>
</fieldset>

<fieldset><legend><strong>{ALLOW_OPENID_LEGEND}</strong></legend>
    <!-- BEGIN allow_openid --><p>{ALLOW_OPENID} {ALLOW_OPENID_LABEL}</p><!-- END allow_openid -->
    <!-- BEGIN allow_openid_note --><p>{ALLOW_OPENID_NOTE}</p><!-- END allow_openid_note -->
</fieldset>

{SUBMIT}
{END_FORM}
