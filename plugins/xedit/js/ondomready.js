   function onDOMReady(fn, ctx){  
        var ready, timer;  
        var onStateChange = function(e){  
            if(e && e.type == "DOMContentLoaded"){  
                fireDOMReady();  
            }else if(e && e.type == "load"){  
                fireDOMReady();  
            }else if(document.readyState){  
                if(document.readyState == "loaded" || document.readyState == "complete"){  
                   fireDOMReady();  
               }else if(!!document.documentElement.doScroll){  
                   try{  
                       ready || document.documentElement.doScroll('left');  
                   }catch(e){  
                       return;  
                   }  
                   fireDOMReady();  
               }  
           }  
       };  
     
       var fireDOMReady = function(){  
           if(!ready){  
               ready = true;  
               fn.call(ctx || window);  
               if(document.removeEventListener)  
                   document.removeEventListener("DOMContentLoaded", onStateChange, false);  
               document.onreadystatechange = null;  
               window.onload = null;  
               clearInterval(timer);  
               timer = null;  
           }  
       };  
     
       if(document.addEventListener)  
           document.addEventListener("DOMContentLoaded", onStateChange, false);  
       document.onreadystatechange = onStateChange;  
       timer = setInterval(onStateChange, 5);  
       window.onload = onStateChange; 

   };  