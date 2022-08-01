<?php class Numeric
{
    static function getPrice($product, $currency_rate = null)
    {
        if (!isset($product['price'])
            || !isset($product['basePrice'])
            || !isset($product['discount'])
        ) {
            return false;
        }
        $product['basePrice'] = substr($product['basePrice'], 0, -3);
        $product['price'] = substr($product['price'], 0, -3);

        $return['discount'] = $product['discount']; // Скидка(float)
        $sale_price_more = '';
        if ($product['discount']>19)
            $sale_price_more = 'sale-price-more';

        if ($product['basePrice'] - $product['price'] == 0) {
            $return = '<div class="base-price">' . $product['basePrice'] . ' ' . $_SESSION['currency']['icon'] . '</div>';
        } else {
            $return = '<div class="base-price-no-sale">' . $product['basePrice'] . '</div> <div class="sale-price '.$sale_price_more.'">' . $product['price'] . ' ' . $_SESSION['currency']['icon'] . '</div>';
        }

        return $return;
    }

    static function formatPrice($price, $currency = '')
    {
        if ($currency == '') $currency = $_SESSION['currency'];
        if (!isset($_SESSION['rounding'])) $_SESSION['rounding'] = 2;
        if ($currency['position'] == 1) $price = '<span>' . number_format($price, $_SESSION['rounding'], ',', ' ') . ' </span> <span>' . $currency['icon'] . '</span>';
        else $price = number_format($price, $_SESSION['rounding'], ',', ' ') . '<span>' . $currency['icon'] . '</span>';
        return $price;
    }
}

