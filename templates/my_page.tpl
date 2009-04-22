<h1>{TITLE}</h1>

<!-- BEGIN message -->
<div class="padded"><h3>{MESSAGE}</h3></div>
<!-- END message -->

<div>
<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{OPENID_IDENTIFIER_SORT}</th>
    <th>{ACTION}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{OPENID_IDENTIFIER}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="2">{EMPTY_MESSAGE}</td>
  </tr>
<!-- END empty_message -->
</table>
</div>

<!-- BEGIN navigation -->
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<!-- END navigation -->

{START_FORM}
{OPENID_IDENTIFIER} {SUBMIT}<br />
<span class="smaller">e.g. http://username.myopenid.com</span>
{END_FORM}