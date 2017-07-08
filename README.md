# ajax-mini-cart
https://www.magentocommerce.com/magento-connect/ajax-mini-cart-ajax-cart.html

Ajax mini cart / Ajax cart
Mini cart extension allow customers to view and manage contents in cart easily. Customers can continue shopping without reloading page. Items in the cart can be managed easily from the Mini cart. It also sync with the cart page using Ajax.

Features:
1. Admin can enable / disable extension according to needs.
2. Allows user to add product to cart without reloading the page / redirecting to Cart page.
3. Ajax -/ + quantity of products from the mini cart.
4. Allow to remove product(s) from the cart.
5. Show user friendly notification message on the top to track adding product progress.

Note: For 1.8 or below version add following code in the your-theme/page/html/header.phtml file :
echo $this->getChildHtml('minicart_head'); 
