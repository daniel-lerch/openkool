/**
 * Created by Andreas Hess @ Lauper Computing on 10/12/14.
 */


/********************* Tooltip ****************************
 * Original from www.leigeber.com adapted by Renzo Lauper *
 /**********************************************************/
var tooltip=function(){
    var id = 'tt';
    var top = 13;
    var left = 3;
    var maxw = 500;
    var speed = 50;
    var timer = 10;
    var endalpha = 100;
    var alpha = 0;
    var tt,t,c,b,ov,oh,pv,ph;
    var ie = document.all ? true : false;
    return{
        show:function(v,w,_pv,_ph){
            pv = _pv ? _pv : 't';
            ph = _ph ? _ph : 'r';
            if(tt == null){
                tt = document.createElement('div');
                tt.setAttribute('id',id);
                t = document.createElement('div');
                t.setAttribute('id',id + 'top');
                c = document.createElement('div');
                c.setAttribute('id',id + 'cont');
                b = document.createElement('div');
                b.setAttribute('id',id + 'bot');
                tt.appendChild(t);
                tt.appendChild(c);
                tt.appendChild(b);
                document.body.appendChild(tt);
                tt.style.opacity = 0;
                tt.style.filter = 'alpha(opacity=0)';
                document.onmousemove = this.pos;
            }
            tt.style.display = 'block';
            c.innerHTML = v;
            tt.style.width = w ? w + 'px' : 'auto';
            if(!w && ie){
                t.style.display = 'none';
                b.style.display = 'none';
                tt.style.width = tt.offsetWidth;
                t.style.display = 'block';
                b.style.display = 'block';
            }
            if(tt.offsetWidth > maxw){tt.style.width = maxw + 'px'}
            ov = (pv == 't' ? parseInt(-1*parseInt(tt.offsetHeight) - top) : (pv == 'm' ? parseInt(-1*tt.offsetHeight/2) : parseInt(top)));
            oh = (ph == 'r' ? parseInt(left) : (ph == 'c' ? parseInt(-1*tt.offsetWidth/2) : parseInt(-1*tt.offsetWidth - left)));
            clearInterval(tt.timer);
            tt.timer = setInterval(function(){tooltip.fade(1)},timer);
        },
        pos:function(e){
            var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
            var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
            tt.style.top = (u + ov) + 'px';
            tt.style.left = (l + oh) + 'px';
        },
        fade:function(d){
            var a = alpha;
            if((a != endalpha && d == 1) || (a != 0 && d == -1)){
                var i = speed;
                if(endalpha - a < speed && d == 1){
                    i = endalpha - a;
                }else if(alpha < speed && d == -1){
                    i = a;
                }
                alpha = a + (i * d);
                tt.style.opacity = alpha * .01;
                tt.style.filter = 'alpha(opacity=' + alpha + ')';
            }else{
                clearInterval(tt.timer);
                if(d == -1){tt.style.display = 'none'}
            }
        },
        hide:function(){
            clearInterval(tt.timer);
            tt.timer = setInterval(function(){tooltip.fade(-1)},timer);
        }
    };
}();
/********************* /Tooltip ****************************/