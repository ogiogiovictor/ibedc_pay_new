<!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>IBEDC PAY</title>
    <style media="all" type="text/css">
    body {
      font-family: Helvetica, sans-serif;
      -webkit-font-smoothing: antialiased;
      font-size: 15px;
      line-height: 1.3;
      background-color: #f4f5f6;
      margin: 0;
      padding: 0;
    }

    .container {
      margin: 0 auto;
      max-width: 900px;
      padding-top: 24px;
    }

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

    p {
      font-family: Helvetica, sans-serif;
      font-size: 15px;
      margin: 0 0 16px;
    }

    .error-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .error-table th {
      background-color: #c0392b;
      color: #ffffff;
      text-align: left;
      padding: 8px 10px;
      border: 1px solid #eaebed;
    }

    .error-table td {
      padding: 8px 10px;
      border: 1px solid #eaebed;
      vertical-align: top;
    }

    .error-table tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    .footer {
      clear: both;
      padding: 24px 0;
      text-align: center;
      color: #9a9ea6;
      font-size: 13px;
    }
    </style>
  </head>
  <body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td>
          <div class="container">

            <table style="width:100%;margin:0 auto;">
              <tbody>
                <tr>
                  <td style="text-align: center; padding-bottom:25px">
                    <a href="#"><img style="height: 40px" src="https://www.ibedc.com/assets/img/logo.png" alt="logo"></a>
                  </td>
                </tr>
              </tbody>
            </table>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main">
              <tr>
                <td class="wrapper">
                  <p><b>DISREP Meters Programming — Error Report</b></p>

                  <p>
                    The scheduled DISREP meter programming run encountered
                    <b>{{ count($errors) }}</b> {{ count($errors) === 1 ? 'error' : 'errors' }}.
                    Details are listed below for review.
                  </p>

                  <table role="presentation" class="error-table">
                    <thead>
                      <tr>
                        <th>Account No</th>
                        <th>Meter No</th>
                        <th>Address</th>
                        <th>Error Message</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($errors as $row)
                        <tr>
                          <td>{{ $row['account_no'] ?: 'N/A' }}</td>
                          <td>{{ $row['meter_no'] ?: 'N/A' }}</td>
                          <td>{{ $row['address'] ?: 'N/A' }}</td>
                          <td>{{ $row['error_message'] ?: 'N/A' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>

                  <p style="margin-top:16px;">Best regards,<br/>Ibadan Electricity Distribution Company (IBEDC)</p>
                </td>
              </tr>
            </table>

            <div class="footer">
              Copyright &copy; {{ date('Y') }} IBEDC. All rights reserved.
            </div>

          </div>
        </td>
      </tr>
    </table>
  </body>
</html>
