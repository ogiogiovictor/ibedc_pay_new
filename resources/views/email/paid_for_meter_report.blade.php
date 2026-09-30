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

    .summary-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .summary-table th {
      background-color: #1a3c6e;
      color: #ffffff;
      text-align: left;
      padding: 8px 10px;
      border: 1px solid #eaebed;
    }

    .summary-table td {
      padding: 8px 10px;
      border: 1px solid #eaebed;
      vertical-align: top;
    }

    .summary-table td.num,
    .summary-table th.num {
      text-align: right;
    }

    .summary-table tr:nth-child(even) td {
      background-color: #f9f9f9;
    }

    .summary-table tr.total-row td {
      background-color: #e8f0fe;
      font-weight: bold;
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
                  <p><b>Paid For Meter Report — {{ now()->format('d M Y') }}</b></p>

                  <p>
                    There are <b>{{ number_format($totals['total']) }}</b> customers who have paid for a meter,
                    are at status 2 and have not yet been assigned an account number. The summary by business hub is below;
                    the full records are in the attached Excel file.
                  </p>

                  <table role="presentation" class="summary-table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Region</th>
                        <th>Business Hub</th>
                        <th class="num">Total</th>
                        <th class="num">With Billing</th>
                        <th class="num">With Regional Billing</th>
                        <th class="num">With Regional Head</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($summary as $i => $row)
                        <tr>
                          <td>{{ $i + 1 }}</td>
                          <td>{{ $row['region'] ?: 'N/A' }}</td>
                          <td>{{ $row['business_hub'] ?: 'N/A' }}</td>
                          <td class="num">{{ number_format($row['total']) }}</td>
                          <td class="num">{{ number_format($row['with_billing']) }}</td>
                          <td class="num">{{ number_format($row['with_regional_billing']) }}</td>
                          <td class="num">{{ number_format($row['with_regional_head']) }}</td>
                        </tr>
                      @endforeach
                      <tr class="total-row">
                        <td colspan="3">Grand Total</td>
                        <td class="num">{{ number_format($totals['total']) }}</td>
                        <td class="num">{{ number_format($totals['with_billing']) }}</td>
                        <td class="num">{{ number_format($totals['with_regional_billing']) }}</td>
                        <td class="num">{{ number_format($totals['with_regional_head']) }}</td>
                      </tr>
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
