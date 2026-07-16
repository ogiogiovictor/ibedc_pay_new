<!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>IBEDC PAY</title>
    <style media="all" type="text/css">
    /* -------------------------------------
    GLOBAL RESETS
------------------------------------- */

    body {
      font-family: Helvetica, sans-serif;
      -webkit-font-smoothing: antialiased;
      font-size: 16px;
      line-height: 1.3;
      -ms-text-size-adjust: 100%;
      -webkit-text-size-adjust: 100%;
    }

    table {
      border-collapse: separate;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
      width: 100%;
    }

    table td {
      font-family: Helvetica, sans-serif;
      font-size: 16px;
      vertical-align: top;
    }
    /* -------------------------------------
    BODY & CONTAINER
------------------------------------- */

    body {
      background-color: #f4f5f6;
      margin: 0;
      padding: 0;
    }

    .body {
      background-color: #f4f5f6;
      width: 100%;
    }

    .container {
      margin: 0 auto !important;
      max-width: 600px;
      padding: 0;
      padding-top: 24px;
      width: 600px;
    }

    .content {
      box-sizing: border-box;
      display: block;
      margin: 0 auto;
      max-width: 600px;
      padding: 0;
    }
    /* -------------------------------------
    HEADER, FOOTER, MAIN
------------------------------------- */

    .main {
      background: #ffffff;
      border: 1px solid #eaebed;
      border-radius: 16px;
      width: 100%;
    }

    .wrapper {
      box-sizing: border-box;
      padding: 24px;
    }

    .footer {
      clear: both;
      padding-top: 24px;
      text-align: center;
      width: 100%;
    }

    .footer td,
    .footer p,
    .footer span,
    .footer a {
      color: #9a9ea6;
      font-size: 16px;
      text-align: center;
    }
    /* -------------------------------------
    TYPOGRAPHY
------------------------------------- */

    p {
      font-family: Helvetica, sans-serif;
      font-size: 16px;
      font-weight: normal;
      margin: 0;
      margin-bottom: 16px;
    }

    a {
      color: #0867ec;
      text-decoration: underline;
    }
    /* -------------------------------------
    OTHER STYLES THAT MIGHT BE USEFUL
------------------------------------- */

    .last {
      margin-bottom: 0;
    }

    .first {
      margin-top: 0;
    }

    .align-center {
      text-align: center;
    }

    .align-right {
      text-align: right;
    }

    .align-left {
      text-align: left;
    }

    .clear {
      clear: both;
    }

    .mt0 {
      margin-top: 0;
    }

    .mb0 {
      margin-bottom: 0;
    }

    .preheader {
      color: transparent;
      display: none;
      height: 0;
      max-height: 0;
      max-width: 0;
      opacity: 0;
      overflow: hidden;
      mso-hide: all;
      visibility: hidden;
      width: 0;
    }

    .info-table {
      width: 100%;
      margin: 16px 0 24px;
      border-collapse: collapse;
    }

    .info-table td {
      padding: 10px 12px;
      border: 1px solid #eaebed;
      font-size: 15px;
    }

    .info-table td.label {
      background-color: #f4f5f6;
      font-weight: bold;
      width: 40%;
    }

    .highlight {
      background-color: #fff8e1;
      border: 1px solid #f1c40f;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 16px;
    }

    /* -------------------------------------
    RESPONSIVE AND MOBILE FRIENDLY STYLES
------------------------------------- */

    @media only screen and (max-width: 640px) {
      .main p,
      .main td,
      .main span {
        font-size: 16px !important;
      }
      .wrapper {
        padding: 8px !important;
      }
      .content {
        padding: 0 !important;
      }
      .container {
        padding: 0 !important;
        padding-top: 8px !important;
        width: 100% !important;
      }
      .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
    }
    /* -------------------------------------
    PRESERVE THESE STYLES IN THE HEAD
------------------------------------- */

    @media all {
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass,
      .ExternalClass p,
      .ExternalClass span,
      .ExternalClass font,
      .ExternalClass td,
      .ExternalClass div {
        line-height: 100%;
      }
      .apple-link a {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        text-decoration: none !important;
      }
      #MessageViewBody a {
        color: inherit;
        text-decoration: none;
        font-size: inherit;
        font-family: inherit;
        font-weight: inherit;
        line-height: inherit;
      }
    }
    </style>
  </head>
  <body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">

          <table style="width:100%;max-width:620px;margin:0 auto;">
                        <tbody>
                            <tr>
                                <td style="text-align: center; padding-bottom:25px">
                                    <a href="#"><img style="height: 40px" src="https://www.ibedc.com/assets/img/logo.png" alt="logo"></a>
                                </td>
                            </tr>
                        </tbody>
            </table>

            <!-- START CENTERED WHITE CONTAINER -->
            <span class="preheader">Your meter has been programmed successfully.</span><br/>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper">
                  <p><b>Dear {{ $landlord_name ?: 'Valued Customer' }},</b></p>

                  <p>
                    We are pleased to inform you that the prepaid meter for the property below has been
                    successfully programmed and is now ready for use.
                  </p>

                  <table role="presentation" class="info-table">
                    <tbody>
                      <tr>
                        <td class="label">Customer Name</td>
                        <td>{{ $customer_name ?: 'N/A' }}</td>
                      </tr>
                      <tr>
                        <td class="label">MAP ID</td>
                        <td>{{ $map_id ?: 'N/A' }}</td>
                      </tr>
                      <tr>
                        <td class="label">Account Number</td>
                        <td>{{ $account_no ?: 'N/A' }}</td>
                      </tr>
                      <tr>
                        <td class="label">Meter Number</td>
                        <td><b>{{ $meter_no ?: 'N/A' }}</b></td>
                      </tr>
                    </tbody>
                  </table>

                  <div class="highlight">
                    <p class="last"><b>Important:</b> Please always vend/purchase electricity using the meter number
                    <b>{{ $meter_no ?: 'N/A' }}</b> to ensure your payment is credited correctly.</p>
                  </div>

                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                      <tr>
                        <td align="left">
                          <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                              <tr>
                                <td>
                                  <p>
                                    Thank you for trusting IBEDC for your energy management needs. We're committed to
                                    providing you with a seamless and secure experience every time.
                                  </p>

                                  <p>Best regards,</p>
                                  Ibadan Electricity Distribution Company (IBEDC)
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                </td>
              </tr>

              <!-- END MAIN CONTENT AREA -->
              </table>


    <br/>
            <!-- START FOOTER -->
            <div class="footer">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block">
                    <span class="apple-link">Copyright © <?php echo date('Y'); ?> IBEDC. All rights reserved. </span>
                  </td>
                </tr>
                <tr>
                  <td class="content-block powered-by">
                    Powered by <a href="#">IBEDC</a>
                  </td>
                </tr>
              </table>
            </div>

            <!-- END FOOTER -->

<!-- END CENTERED WHITE CONTAINER --></div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>
</html>
