<?php
$pageTitle = "Tax Invoice | Zenkit Official Invoice";
require_once __DIR__ . '/includes/helpers.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$orderNumber = trim($_GET['order_number'] ?? '');
$db = getDB();

if ($orderId > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
} elseif (!empty($orderNumber)) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
} else {
    $order = null;
}

if (!$order) {
    header("Location: index.php");
    exit;
}

$itemStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

// Calculations for GST Tax Breakdown (18% GST incorporated)
$grandTotal = (float)$order['final_amount'];
$taxableValue = round($grandTotal / 1.18, 2);
$totalGst = round($grandTotal - $taxableValue, 2);
$cgst = round($totalGst / 2, 2);
$sgst = round($totalGst / 2, 2);

// Number to Words function
function convertNumberToWords($number) {
    $words = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
        20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
        60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    ];
    $int = (int)$number;
    if ($int < 20) return $words[$int] ?? '';
    if ($int < 100) return $words[10 * floor($int / 10)] . ($int % 10 ? ' ' . $words[$int % 10] : '');
    if ($int < 1000) return $words[floor($int / 100)] . ' Hundred' . ($int % 100 ? ' and ' . convertNumberToWords($int % 100) : '');
    if ($int < 100000) return convertNumberToWords(floor($int / 1000)) . ' Thousand' . ($int % 1000 ? ' ' . convertNumberToWords($int % 1000) : '');
    return (string)$int;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tax Invoice - <?= htmlspecialchars($order['order_number']) ?> | <?= STORE_NAME ?></title>
  <style>
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      background: #E2E8F0;
      color: #1E293B;
      margin: 0;
      padding: 30px;
      font-size: 13px;
      line-height: 1.4;
    }
    .invoice-wrapper {
      max-width: 820px;
      margin: 0 auto;
      background: #FFFFFF;
      padding: 40px;
      border: 1px solid #CBD5E1;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
      position: relative;
    }
    .header-table, .meta-table, .items-table {
      width: 100%;
      border-collapse: collapse;
    }
    .meta-table td {
      vertical-align: top;
      padding: 10px 0;
    }
    .items-table {
      margin: 20px 0;
    }
    .items-table th {
      background: #F1F5F9;
      border: 1px solid #CBD5E1;
      padding: 8px 10px;
      text-align: left;
      font-size: 11px;
      text-transform: uppercase;
      font-weight: 700;
    }
    .items-table td {
      border: 1px solid #CBD5E1;
      padding: 8px 10px;
      font-size: 12px;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-paid {
      display: inline-block;
      border: 2px solid #16A34A;
      color: #16A34A;
      font-weight: 900;
      font-size: 16px;
      padding: 4px 14px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .btn-actions {
      max-width: 820px;
      margin: 0 auto 20px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      background: #2874F0;
      color: #FFFFFF;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      font-weight: 700;
      cursor: pointer;
    }
    @media print {
      body { background: #FFFFFF; padding: 0; }
      .invoice-wrapper { box-shadow: none; border: none; padding: 0; width: 100%; }
      .btn-actions { display: none; }
    }
  </style>
</head>
<body>

  <!-- Controls -->
  <div class="btn-actions">
    <a href="order-confirmation.php?id=<?= $order['id'] ?>" style="color: #2874F0; font-weight: 700; text-decoration: none;">&larr; Back to Order</a>
    <div>
      <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
    </div>
  </div>

  <div class="invoice-wrapper">
    
    <!-- Top Header -->
    <table class="header-table" style="border-bottom: 2px solid #1E293B; padding-bottom: 16px; margin-bottom: 20px;">
      <tr>
        <td style="width: 60%;">
          <div style="font-size: 26px; font-weight: 900; color: #2874F0; letter-spacing: -1px;">Zenkit<span>✦</span></div>
          <div style="font-size: 11px; color: #64748B; font-weight: 700; text-transform: uppercase;">TAX INVOICE / CASH MEMO</div>
          <div style="font-size: 11px; color: #334155; margin-top: 6px;">
            <strong><?= STORE_NAME ?></strong><br>
            <?= STORE_ADDRESS ?><br>
            GSTIN: <strong><?= STORE_GSTIN ?></strong> | CIN: <?= STORE_CIN ?><br>
            Email: <?= STORE_EMAIL ?> | Phone: <?= STORE_PHONE ?>
          </div>
        </td>
        <td class="text-right" style="vertical-align: top;">
          <div class="badge-paid"><?= htmlspecialchars($order['payment_status']) ?></div>
          <div style="font-size: 11px; margin-top: 8px; color: #475569;">
            Invoice No: <strong>INV-<?= htmlspecialchars($order['order_number']) ?></strong><br>
            Invoice Date: <strong><?= date('d-M-Y', strtotime($order['created_at'])) ?></strong><br>
            Order ID: <strong><?= htmlspecialchars($order['order_number']) ?></strong><br>
            State: <strong>Karnataka (Code: 29)</strong>
          </div>
        </td>
      </tr>
    </table>

    <!-- Billing & Shipping Details -->
    <table class="meta-table">
      <tr>
        <td style="width: 50%; padding-right: 20px;">
          <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #64748B; margin-bottom: 4px;">Billed To / Customer Details</div>
          <div style="font-weight: 700; font-size: 14px;"><?= htmlspecialchars($order['customer_name']) ?></div>
          <div style="color: #475569;"><?= htmlspecialchars($order['shipping_address']) ?></div>
          <div style="color: #475569;"><?= htmlspecialchars($order['city']) ?> - <?= htmlspecialchars($order['postal_code']) ?></div>
          <div style="color: #475569;">Phone: <?= htmlspecialchars($order['customer_phone']) ?></div>
          <div style="color: #475569;">Email: <?= htmlspecialchars($order['customer_email']) ?></div>
        </td>
        <td style="width: 50%; padding-left: 20px; border-left: 1px dashed #CBD5E1;">
          <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: #64748B; margin-bottom: 4px;">Shipping & Logistics Details</div>
          <div>Carrier Partner: <strong>Zenkit Express Logistics (BlueDart / Delhivery)</strong></div>
          <div>Tracking AWB No: <strong style="font-family: monospace;"><?= htmlspecialchars($order['tracking_code']) ?></strong></div>
          <div>Payment Channel: <strong><?= htmlspecialchars($order['payment_method']) ?></strong></div>
          <div>Place of Supply: <strong><?= htmlspecialchars($order['city']) ?></strong></div>
        </td>
      </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
      <thead>
        <tr>
          <th style="width: 30px;">#</th>
          <th>Item Description</th>
          <th style="width: 80px;">HSN Code</th>
          <th class="text-right" style="width: 50px;">Qty</th>
          <th class="text-right" style="width: 80px;">Rate</th>
          <th class="text-right" style="width: 80px;">Taxable</th>
          <th class="text-right" style="width: 60px;">GST</th>
          <th class="text-right" style="width: 90px;">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $i = 1;
        foreach ($items as $item): 
          $itemTotal = (float)$item['subtotal'];
          $itemTaxable = round($itemTotal / 1.18, 2);
          $itemGst = round($itemTotal - $itemTaxable, 2);
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td>
              <strong><?= htmlspecialchars($item['product_name']) ?></strong>
              <div style="font-size: 10px; color: #64748B;">Serial/IMEI: ZK-<?= strtoupper(substr(md5($item['id'] . $order['id']), 0, 10)) ?> • 1 Year Mfr Warranty</div>
            </td>
            <td>84713010</td>
            <td class="text-right"><?= $item['quantity'] ?></td>
            <td class="text-right"><?= formatPrice($item['price']) ?></td>
            <td class="text-right"><?= formatPrice($itemTaxable) ?></td>
            <td class="text-right">18%</td>
            <td class="text-right"><strong><?= formatPrice($itemTotal) ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Tax Summary & Grand Total -->
    <table style="width: 100%; margin-top: 10px;">
      <tr>
        <td style="width: 55%; vertical-align: top;">
          <div style="font-size: 11px; color: #475569; margin-bottom: 10px;">
            <strong>Amount in Words:</strong><br>
            US Dollars <?= convertNumberToWords(floor($grandTotal)) ?> and <?= round(($grandTotal - floor($grandTotal)) * 100) ?>/100 Only.
          </div>
          <div style="border: 1px dashed #CBD5E1; padding: 10px; font-size: 11px; color: #64748B; border-radius: 4px;">
            <strong>Declaration:</strong><br>
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct. Goods once sold are covered under 7 days replacement and standard 1-year brand warranty.
          </div>
        </td>
        <td style="width: 45%; vertical-align: top; padding-left: 20px;">
          <table style="width: 100%; font-size: 12px;">
            <tr>
              <td style="padding: 4px 0;">Total Taxable Amount:</td>
              <td class="text-right"><strong><?= formatPrice($taxableValue) ?></strong></td>
            </tr>
            <tr>
              <td style="padding: 4px 0; color: #64748B;">CGST (9.0%):</td>
              <td class="text-right"><?= formatPrice($cgst) ?></td>
            </tr>
            <tr>
              <td style="padding: 4px 0; color: #64748B;">SGST (9.0%):</td>
              <td class="text-right"><?= formatPrice($sgst) ?></td>
            </tr>
            <?php if ($order['discount_amount'] > 0): ?>
              <tr>
                <td style="padding: 4px 0; color: #16A34A;">Coupon Discount Applied:</td>
                <td class="text-right" style="color: #16A34A;">- <?= formatPrice($order['discount_amount']) ?></td>
              </tr>
            <?php endif; ?>
            <tr style="border-top: 2px solid #1E293B; font-size: 15px;">
              <td style="padding: 8px 0; font-weight: 900;">Grand Total:</td>
              <td class="text-right" style="padding: 8px 0; font-weight: 900; color: #2874F0;"><?= formatPrice($grandTotal) ?></td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Signature & Footer -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 20px; border-top: 1px solid #E2E8F0;">
      <div>
        <div style="width: 80px; height: 80px; border: 2px solid #1E293B; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 9px; font-weight: 800; text-align: center; padding: 4px;">
          <span>VERIFIED</span>
          <span style="font-size: 14px;">QR</span>
          <span>TAX AUTH</span>
        </div>
        <div style="font-size: 9px; color: #94A3B8; margin-top: 4px;">System Generated Digital Invoice</div>
      </div>

      <div class="text-right">
        <div style="font-size: 12px; font-weight: 800; color: #1E293B;">For <?= STORE_NAME ?></div>
        <div style="font-style: italic; color: #2874F0; font-weight: 700; margin: 12px 0 4px 0;">Authorized Signatory</div>
        <div style="font-size: 10px; color: #64748B;">Computer Generated - No Physical Signature Required</div>
      </div>
    </div>

  </div>

</body>
</html>
