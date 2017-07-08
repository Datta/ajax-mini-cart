var ajaxcart = {
    g: new Growler(),
    initialize: function() {
        this.g = new Growler();        
        this.bindEvents();
    },
    bindEvents: function () {
        this.addSubmitEvent();               
        $$('a[href*="/checkout/cart/delete/"]').each(function(e){
            $(e).observe('click', function(event){
                setLocation($(e).readAttribute('href'));
                Event.stop(event);
            });
        });
    },
    ajaxCartSubmit: function (obj,id) {
        var _this = this;
        
        if(Modalbox !== 'undefined' && Modalbox.initialized)Modalbox.hide();

        try {  
            if(typeof obj == 'string') {
                var url = obj;
				
                //AjaxQueue.push(url, {onSucess: funcSuccess, onfailure: funcFailure});
                var option = url.split("="); 
                var key ='';
                var value ='';
                var optstr ='';
                if(option){
                    if(option[1]){
                        var optval = option[1].split(',');
                        key = optval[0];
                        value = optval[1];                       
                        optstr = 'options['+key+']='+value;                         
                    }
                }  
                AjaxQueue.push(url, {  
                                                
                    parameters: optstr,                    
                    onLoading : function(response) {                   
                        $('load_animate').show();    				       
                    },
					onLoaded : function(response) {                   
                            $("#note").hide();
				_this.showError('');
                    },
                                     
                    onSuccess    : function(response) {
                        
                         // $(".Growler-notice-body").hide();	                       
                        // Handle the response content...                        
                        try{
                            var res = response.responseText.evalJSON(); 
                                  
                            if(res) { 
                                  
                                if(typeof res.carttotal != 'undefined') {                                   
                                    if(res.carttotal!=0){ 
                                        //$('mini-cart-total').update('$'+parseFloat(res.carttotal).toFixed(2));                         
                                       document.getElementById("mini-cart-total").innerHTML ='$'+parseFloat(res.cartsubtotal).toFixed(2);    
                                       
                                        $('minicartfooterid').show();   
                                       
                                    }else{                                        
                                       document.getElementById("mini-cart-total").innerHTML ='$0.00';  
                                       
                                       $('minicartfooterid').hide();
                                    }                                       
                                }
                                 if(typeof res.cartcount != 'undefined') {                             
                                    if(parseInt(res.cartcount)==0){
                                        document.getElementById("header-text").innerHTML = "No item in cart to display";   
                                    }else{ 
                                        document.getElementById("header-text").innerHTML = "Showing "+ parseInt(res.cartcount) +" of " +parseInt(res.cartcount)+" items added";     
                                    }                                    
                                  }                       
                                    
                                if(typeof res.cartitem != 'undefined') {                             
                                    document.getElementById("mini-cart-summary").innerHTML =res.cartitem;                                         
                                   
                                  }                                  
                                var currentUrl = window.location.href;         
                                   if( currentUrl.search('/checkout/') != -1 ) {                           
                                     if(typeof res.cartsubtotal != 'undefined') {  
                                           var subTotalobje = document.getElementById("sub-total");                         
                                           var subTotalobje1 = document.getElementById("grand-total");                         
                                           if( subTotalobje != null && subTotalobje1 != null){
                                           if(res.carttotal!=0){                            
                                                document.getElementById("sub-total").innerHTML ='$'+parseFloat(res.cartsubtotal).toFixed(2);                                        
                                                document.getElementById("grand-total").innerHTML ='$'+parseFloat(res.carttotal).toFixed(2);
                                            }else{
                                                document.getElementById("sub-total").innerHTML ='$0.00';
                                                document.getElementById("grand-total").innerHTML ='$0.00'; 
                                            }
                                        }
                                      }                                                                                                                                                                                                                                                        
                                     
                                      if(typeof res.shippingtaxamount != 'undefined' && res.shippingtaxamount !=0) { 
                                            var taxamtObj =document.getElementById("shippingtax"); 
                                            if(taxamtObj != null){
                                                if(parseInt(res.shippingtaxamount)==0){                            
                                                    document.getElementById("shippingtax").innerHTML = '$'+parseFloat(res.shippingtaxamount);
                                                }else{  
                                                    document.getElementById("shippingtax").innerHTML = '$'+parseFloat(res.shippingtaxamount);
                                                }                     
                                            }                   
                                      }
                                        
                                     if(typeof res.discount != 'undefined') {
                                          var discountObj = document.getElementById("discountamount"); 
                                           if(discountObj != null){
                                              if(parseInt(res.discount)==0){                           
                                                    document.getElementById("discountamount").innerHTML = res.discount;
                                              }else{  
                                                    document.getElementById("discountamount").innerHTML = res.discount;
                                              }
                                           } 
                                    
                                     }
                                   }
                                 
                                  if( currentUrl.search('checkout/onepage') != -1 ) {   
                                      if(typeof res.cartcount != 'undefined') {
                                        var checoutCntObj = document.getElementById("checout-count");  
                                         if(checoutCntObj != null){                            
                                            if(parseInt(res.cartcount)==0){  
                                                document.getElementById("checout-count").innerHTML = parseInt(res.cartcount);  
                                            }else{                                        
                                                           
                                                document.getElementById("checout-count").innerHTML = parseInt(res.cartcount);
                                            }
                                         }                                    
                                    } 
                                  }
                                //check for group product's option
                                if(res.configurable_options_block) {
                                    if(res.r == 'success') {        
                                                   
                                        //show group product options block
                                        _this.showPopup(res.configurable_options_block);
                                    } else {                                        
                                        if(typeof res.messages != 'undefined') {  
                                            _this.showError(res.messages);
                                        } else {
                                           // _this.showError("Something bad happened");
                                        }
                                    }
                                } else { 
                                    
                                    if(res.r == 'success') {
                                        if(res.message) {                                              
                                            _this.showSuccess(res.message);
                                        } else {
                                           _this.showSuccess('Item was added into cart.');
                                        }

                                        //update all blocks here
                                        _this.updateBlocks(res.update_blocks);

                                    } else {
                                        
                                        if(typeof res.messages != 'undefined') {                                           
                                       //     alert('iii'+id);
                                          setTimeout(function(){ if(id){
											    var eleitem; 
                                                var qty = document.getElementById("cart_qty_"+parseInt(id)).value-1;  
                                               // alert('qty'+qty);               
                                                if(qty==0){
                                                   document.getElementById("cart_qty_"+parseInt(id)).value=1; 
                                                   eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
                                                   if(eleitem){
                                                        document.getElementById("cart-itemqty-"+parseInt(id)).value=1;  
                                                   }
                                                }else if(qty>0){           
                                                   document.getElementById("cart_qty_"+parseInt(id)).value=qty;
                                                   eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
                                                   if(eleitem){
                                                       eleitem.value=qty;              
                                                   }
                                                }
                                           }
                                          },3000);
                                           _this.showError(res.messages);
                                           
                                        } else {
                                           // _this.showError("Something bad happened");
                                        }
                                    }
                                }
                            } else {
                                document.location.reload(true);
                            }
                        } catch(e) {
                        //window.location.href = url;
                        //document.location.reload(true);
                        }
                        $('load_animate').hide();
                    },
                    onComplete: function() {	
                        //alert('hello your requrest is completed');
                        $('load_animate').hide();
						
                     }
                }); 
            } else {
                if(typeof obj.form.down('input[type=file]') != 'undefined') {

                    //use iframe                                                 
                    obj.form.insert('<iframe id="upload_target" name="upload_target" src="" style="width:0;height:0;border:0px solid #fff;"></iframe>');

                    var iframe = $('upload_target');
                    iframe.observe('load', function(){
                        // Handle the response content...
                        try{
                            var doc = iframe.contentDocument ? iframe.contentDocument : (iframe.contentWindow.document || iframe.document);
                            console.log(doc);
                            var res = doc.body.innerText ? doc.body.innerText : doc.body.textContent;
                            res = res.evalJSON();
                             
                            if(res) {
                                if(res.r == 'success') {   
                                    if(res.message) {
                                        _this.showSuccess(res.message);
                                    } else {
                                        _this.showSuccess('Item was added into cart.');
                                    }

                                    //update all blocks here
                                    _this.updateBlocks(res.update_blocks);

                                } else {  
                                    if(typeof res.messages != 'undefined') {
                                        _this.showError(res.messages);
                                    } else {
                                       // _this.showError("Something bad happened");
                                    }
                                }
                            } else {
                               // _this.showError("Something bad happened");
                            }
                        } catch(e) {
                            console.log(e);
                           // _this.showError("Something bad happened");
                        }
                    });

                    obj.form.target = 'upload_target';

                    //show loading
                    _this.g.warn("Processing", {
                        life: 3
                    });

                    obj.form.submit();
                    return true;

                } else {
                    //use ajax

                    var url     =     obj.form.action,
                    data =    obj.form.serialize();
                     
                    new Ajax.Request(url, {
                        method        : 'post',
						dataType: 'json',
                        postBody    : data,  
                        onCreate    : function() {
                          
                        },
                        onLoading : function(response) {                    
                        $('load_animate').show();                        
                         },
                        onSuccess    : function(response) {  
                            // Handle the response content...
                            try{
                                var res = response.responseText.evalJSON();                                   
                                if(res) {   
                                
                                     if(typeof res.carttotal != 'undefined') {                             
                                       if(res.carttotal!=0){                            
                                            document.getElementById("mini-cart-total").innerHTML ='$'+parseFloat(res.cartsubtotal).toFixed(2);
                                            $('minicartfooterid').show();                     
                                        }else{
                                            document.getElementById("mini-cart-total").innerHTML ='$0.00'; 
                                             $('minicartfooterid').hide(); 
                                        }
                                      }
                                      
                                      if(typeof res.cartitem != 'undefined') {    
                                      
                                        document.getElementById("mini-cart-summary").innerHTML =res.cartitem;  
                                        
                                        //$$("#mini-cart-summary").insert(res.cartitem);    
                                        //document.getElementById("mini-cart-summary").appendChild(res.cartitem);     
                                      } 
                                       if(typeof res.cartcount != 'undefined') {                             
                                            if(parseInt(res.cartcount)==0){
                                                document.getElementById("header-text").innerHTML = "No item in cart to display";  
                                                
                                            }else{ 
                                                document.getElementById("header-text").innerHTML = "Showing "+ parseInt(res.cartcount) +" of " +parseInt(res.cartcount)+" items added";     
                                                                                                
                                            }                                    
                                          }                     
                                     var currentUrl = window.location.href;                        
                                      // cart page data update                      
                                        if( currentUrl.search('/checkout/') != -1 ) {                           
                                             if(typeof res.cartsubtotal != 'undefined') {  
                                                   var subTotalobje = document.getElementById("sub-total");                         
                                                   var subTotalobje1 = document.getElementById("grand-total");                         
                                                   if( subTotalobje != null && subTotalobje1 != null){
                                                   if(res.carttotal!=0){                            
                                                        document.getElementById("sub-total").innerHTML ='$'+parseFloat(res.cartsubtotal).toFixed(2);                                        
                                                        document.getElementById("grand-total").innerHTML ='$'+parseFloat(res.carttotal).toFixed(2);
                                                    }else{
                                                        document.getElementById("sub-total").innerHTML ='$0.00';
                                                        document.getElementById("grand-total").innerHTML ='$0.00'; 
                                                    }
                                                }
                                              }
                                     
                                          if(typeof res.shippingtaxamount != 'undefined' && res.shippingtaxamount !=0) { 
                                                var taxamtObj =document.getElementById("shippingtax"); 
                                                if(taxamtObj != null){
                                                    if(parseInt(res.shippingtaxamount)==0){                            
                                                        document.getElementById("shippingtax").innerHTML = '$'+parseFloat(res.shippingtaxamount);
                                                    }else{  
                                                        document.getElementById("shippingtax").innerHTML = '$'+parseFloat(res.shippingtaxamount);
                                                    }                     
                                                }                   
                                          }
                                        }
                                        
                                         if(typeof res.discount != 'undefined') {
                                              var discountObj = document.getElementById("discountamount"); 
                                               if(discountObj != null){
                                                  if(parseInt(res.discount)==0){                           
                                                        document.getElementById("discountamount").innerHTML = res.discount;
                                                  }else{  
                                                        document.getElementById("discountamount").innerHTML = res.discount;
                                                  }
                                               } 
                                        
                                    
                                     }                                  
                                       
                                    
                                    if(res.r == 'success') { 
                                        if(res.message) {
                                            _this.showSuccess(res.message);
                                        } else {
                                            _this.showSuccess('Item was added into cart.');
                                        }

                                        //update all blocks here
                                        _this.updateBlocks(res.update_blocks);

                                    } else {
                                        if(typeof res.messages != 'undefined') {
                                            
                                            _this.showError(res.messages);
                                        } else {
                                           // _this.showError("Something bad happened");
                                        }
                                    }
                                } else {
                                  //  _this.showError("Something bad happened");
                                }
                            } catch(e) {
                                console.log(e);
                               // _this.showError("Something bad happened");
                            }
                            $('load_animate').hide();
                        },
                        onComplete: function() { 
                            //alert('hello your requrest is completed');
                            $('load_animate').hide();
                         }
                    });
                }
            }
        } catch(e) {
            console.log(e);
            if(typeof obj == 'string') {
                window.location.href = obj;
            } else {
                document.location.reload(true);
            }
        }
    },
    
    getConfigurableOptions: function(url) {
        var _this = this;
        new Ajax.Request(url, {
            onCreate    : function() {               
            },
            onSuccess    : function(response) {  
                // Handle the response content...
                try{
                    var res = response.responseText.evalJSON();
                    if(res) {
                        if(res.r == 'success') {
                            
                            //show configurable options popup
                            _this.showPopup(res.configurable_options_block);

                        } else {
                            if(typeof res.messages != 'undefined') {
                                _this.showError(res.messages);
                            } else {
                                _this.showError("Something bad happened");
                            }
                        }
                    } else {
                        document.location.reload(true);
                    }
                } catch(e) {
                window.location.href = url;
                //document.location.reload(true);
                }
            }
        });
    },

    showSuccess: function(message) {
        notifyBar(message);
       /*  this.g.info(message, {
            life: 0
        });*/
    },

    showError: function (error) {   
        var _this = this;                 
        if(typeof error == 'string') {         
            notifyBar(error);                
           /*  _this.g.error(error, {
                life: 5
            }); */
        } else {
            notifyBar(error);                   
           /* error.each(function(message){                            
                _this.g.error(message, {
                    life: 5
                });
            });*/
        }
         
    },

    addSubmitEvent: function () {
        
        if(typeof productAddToCartForm != 'undefined') {
            var _this = this;
            productAddToCartForm.submit = function(url){                
                if(this.validator && this.validator.validate()){
                    _this.ajaxCartSubmit(this);
                }
                return false;
            }                                  
            productAddToCartForm.form.onsubmit = function() {            
                productAddToCartForm.submit();
                return false;
            };
        }
    },

    updateBlocks: function(blocks) {
        var _this = this;    
        if(blocks) {
            try{
                blocks.each(function(block){
                    if(block.key) {
                        var dom_selector = block.key;
                        if($$(dom_selector)) {
                            $$(dom_selector).each(function(e){
                                $(e).replace(block.value);
                            });
                        }
                    }
                });
                _this.bindEvents();

                // show details tooltip
                truncateOptions();
            } catch(e) {
                console.log(e);
            }
        }  
    },
    
    showPopup: function(block) {
        try {
            var _this = this;
            //$$('body')[0].insert({bottom: new Element('div', {id: 'modalboxOptions'}).update(block)});
            var element = new Element('div', {
                id: 'modalboxOptions'
            }).update(block);
            
            var viewport = document.viewport.getDimensions();
            Modalbox.show(element,
            {
                title: 'Please Select Options', 
                width: 510,
                height: viewport.height,
                afterLoad: function() {
                    _this.extractScripts(block);
                    _this.bindEvents();
                }
            });
        } catch(e) {
            console.log(e)
        }
    },
    
    extractScripts: function(strings) {
        var scripts = strings.extractScripts();
        scripts.each(function(script){
            try {
                eval(script.replace(/var /gi, ""));
            }
            catch(e){
                console.log(e);
            }
        });
    }

};
  
var AjaxQueue = {
    batchSize: 1, //No.of simultaneous AJAX requests allowed, Default : 1
    urlQueue: [], //Request URLs will be pushed into this array
    elementsQueue: [], //Element IDs of elements to be updated on completion of a request ( as in Ajax.Updater )
    optionsQueue: [], //Request options will be pushed into this array
    setBatchSize: function(bSize){ //Method to set a different batch size. Recommended: Set batchSize before making requests
        this.batchSize = bSize;
    },
    push: function(url, options, elementID){ //Push the request in the queue. elementID is optional and required only for Ajax.Updater calls
        this.urlQueue.push(url);
        this.optionsQueue.push(options);
        if(elementID!=null){
            this.elementsQueue.push(elementID);
        } else {
            this.elementsQueue.push("NOTSPECIFIED");
        }
 
        this._processNext();
    },
    _processNext: function() { // Method for processing the requests in the queue. Private method. Don't call it explicitly
        if(Ajax.activeRequestCount < AjaxQueue.batchSize) // Check if the currently processing request count is less than batch size
        {
            if(AjaxQueue.elementsQueue.first()=="NOTSPECIFIED") { //Check if an elementID was specified
                // Call Ajax.Request if no ElementID specified
                //Call Ajax.Request on the first item in the queue and remove it from the queue
                new Ajax.Request(AjaxQueue.urlQueue.shift(), AjaxQueue.optionsQueue.shift());
 
                var junk = AjaxQueue.elementsQueue.shift();
            } else {
                // Call Ajax.Updater if an ElementID was specified.
                //Call Ajax.Updater on the first item in the queue and remove it from the queue
                new Ajax.Updater(AjaxQueue.elementsQueue.shift(), AjaxQueue.urlQueue.shift(), AjaxQueue.optionsQueue.shift());
            }
        }
    }
};
Ajax.Responders.register({
  //Call AjaxQueue._processNext on completion ( success / failure) of any AJAX call.
  onComplete: AjaxQueue._processNext
});

var oldSetLocation = setLocation;
var setLocation = (function() {
    return function(url,id){          
        if (window.location.protocol == "https:") {           
            url=url.replace("http://","https://");      
        } 
        if( url.search('checkout/cart/add') != -1 ) { 
            //its simple/group/downloadable product
            ajaxcart.ajaxCartSubmit(url,id);
        } else if( url.search('checkout/cart/delete') != -1 ) {   
            ajaxcart.ajaxCartSubmit(url);
        } else if( url.search('options=cart') != -1 ) {              
            //its configurable/bundle product
            url += '&ajax=true';
            ajaxcart.getConfigurableOptions(url);
        } else {  
            oldSetLocation(url);
        }
        if(id){            
            var qty = parseInt(document.getElementById("cart_qty_"+ parseInt(id)).value)+1;
            var eleitem;
            if(qty==0){
               document.getElementById("cart_qty_"+parseInt(id)).value=1; 
               eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
               if(eleitem){
                    document.getElementById("cart-itemqty-"+parseInt(id)).value=1;  
               }
            }else if(qty>0){           
               document.getElementById("cart_qty_"+parseInt(id)).value=qty;
               eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
              
               if(eleitem){                 
                setTimeout(function(){
                   document.getElementById("cart-itemqty-"+parseInt(id)).value=qty;    
                },1000);                           
               }
            }
        }
    };
})();

var setDeleteqty = (function() {
    return function(id,url){
        if (window.location.protocol == "https:") {           
            url=url.replace("http://","https://");      
        }
        var qtyObj = document.getElementById("cart_qty_"+parseInt(id)); 
        if(qtyObj != null){             
            var qty = parseInt(qtyObj.value)-1;        
        }else{            
           var qty = parseInt(document.getElementById("cart-itemqty-"+ parseInt(id)).value)-1; 
        }
        if(qty>=1){           
            url = url + 'qty/'+ parseInt(qty)+"/";         
            ajaxcart.ajaxCartSubmit(url);      
        }
        var eleitem;        
        if(qty==0){
           document.getElementById("cart_qty_"+parseInt(id)).value=1; 
           eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
           if(eleitem){
                document.getElementById("cart-itemqty-"+parseInt(id)).value=1;  
           }
        }else if(qty>0){           
           document.getElementById("cart_qty_"+parseInt(id)).value=qty;
           eleitem = document.getElementById("cart-itemqty-"+parseInt(id));
           if(eleitem){
                setTimeout(function(){ 
                document.getElementById("cart-itemqty-"+parseInt(id)).value=qty; 
               },1000);             
           }
        }
    };
})();   

var setDeleteitem = (function() {
    return function(id,url){
          if (window.location.protocol == "https:") {           
            url=url.replace("http://","https://");      
        } 
         var qty = parseInt(document.getElementById("cart_qty_"+parseInt(id)).value)-1;  
         url = url +"qty/0/";
         ajaxcart.ajaxCartSubmit(url);
        
         var elem = document.getElementById("li-"+id);
         if(elem){
            elem.remove();
         }
         var elem2 = document.getElementById("item-"+id);
         if(elem2){
            elem2.remove();
         }
         
    };
})();

setPLocation = setLocation;    
document.observe("dom:loaded", function() {
    ajaxcart.initialize();
});

function notifyBar(msg) {     
    //jQuery     
    jQuery.noConflict();   
    jQuery('.notification').show('fast');
    jQuery('.notification').html("<div id='note'>"+msg+"</div>");     
     setTimeout(function(){         
        jQuery(".notification").hide('slow');
      },1500);       
 } 
 jQuery.noConflict(); 
jQuery(function($) {   
    jQuery('.header-cart .relative').hoverIntent({
        over: function() {           
            jQuery(this).children('.cart-items-box').slideDown(300); 
            jQuery('.cart-items-box').show();           
        },
        out: function() {            
            jQuery(this).children('.cart-items-box').slideUp(300);           
            jQuery('.cart-items-box').hide();
        }
    });    
    
     /* cart page delete operation  */
    $(".cart-item-box td.last a" ).click(function() {      
      // $(this).closest('tr').remove();
       $(this).closest('tr').hide('slow');
       var id ="li-"+$(this).attr('id');              
       if(id){            
            $("#"+id).hide('slow');            
       }         
    });
    $("li a.remove-btn" ).click(function() {      
       $(this).closest('li').hide('slow'); 
       var id ="item-"+$(this).attr('id');        
       if(id){
          $("#"+id).hide('slow'); 
       }
    });    
    
     
 });