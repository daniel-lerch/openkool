// Select Menu 3

// Copyright Xin Yang 2004
// Web Site: www.yxScripts.com
// EMail: m_yangxin@hotmail.com
// Last Updated: 2004-07-22

// This script is free as long as the copyright notice remains intact.

// ------
var smListPool={}, itemSepRE=/\s*,\s*/, rangeSepRE=/\s*:\s*/;

function smTrimIt(string) {
  return string.replace(/^\s+/, "").replace(/\s+$/, "");
}

function smGetItems(item_list) {
  var item_pool=new Array();

  var items=smTrimIt(item_list).split(itemSepRE);
  for (var i=0; i<items.length; i++) {
    if (items[i].indexOf(":")==-1) {
      item_pool[item_pool.length]=items[i];
    }
    else {
      var range=items[i].split(rangeSepRE);
      for (var j=parseInt(range[0],10); j<=parseInt(range[1],10); j++) {
        item_pool[item_pool.length]=j;
      }
    }
  }

  return item_pool;
}

function smListOBJ(id, non_item_tag, sub_tag, back_tag) {
  this.id=id;
  this.non_item_tag=non_item_tag;
  this.sub_tag=sub_tag;
  this.back_tag=back_tag;

  this.list=null;
  this.top_idx=0; this.top_list=[];
  this.item_pool={}; this.path={};
}

function smOptionItemOBJ(id, num, value, desc) {
  this.type="I";
  this.id=id;
  this.num=num;
  this.value=value;
  this.desc=desc;
}

function smSubListItemOBJ(id, num, idx, desc, item_list) {
  this.type="M";
  this.id=id;
  this.num=num;
  this.idx=idx;
  this.desc=desc;

  this.item_pool=smGetItems(item_list);
}

function smEmptyList(id) {
  var list=smListPool[id].list;
  $(list).html('');
}

function smUpdateList() {
  var $this = $(this);
  var id=this.list, list=smListPool[id], option=$this.data('value')+"";

  if (option==list.back_tag) {
    smEmptyList(id);
    smSetTopList(id, -1);
  }
  else if (option.substring(0,list.sub_tag.length)==list.sub_tag) {
    var sub=option.substring(list.sub_tag.length+1);
    smEmptyList(id);
    smSetSubList(id, sub);
  }
  else {
    smSetCookie(id+"-idx", $this.children().index($this.children('.active')[0]));
  }
}

function optionOBJ(text, value) {
  this.text=text; this.value=value;
}

function smSetList(list, options, selected) {
  var $list = $(list);
  for (var i=0; i<options.length; i++) {
    $list.append(getSelectOption(options[i].value, options[i].text));
  }
  if (selected>=0) {
    $($list.children()[selected]).addClass(doubleSelectActiveClass);
  }
}

function smGetList(id, num) {
  var pool=smListPool[id], options=new Array(), list=pool.top_list;

  if (num!="" && pool.item_pool[num] && pool.item_pool[num].type=="M") {
    list=pool.item_pool[num].item_pool;
  }

  for (i=0; i<list.length; i++) {
    var item=pool.item_pool[list[i]];
    if (item) {
      if (item.type=="I") {
        options[i]=new optionOBJ(item.desc, item.value);
      }
      else {
        options[i]=new optionOBJ(item.desc, pool.sub_tag+":"+item.num);
      }
    }
  }

  return options;
}

// mode: 0:use cookie index or default if cookie not found, 1:use default, -1:no selected
function smSetTopList(id, mode) {
  var pool=smListPool[id], options=smGetList(id, "");

  if (mode==-1) {
    smSetList(pool.list, options, -1);
    smSetCookie(id+"-type", ""); smSetCookie(id+"-idx", "");
  }
  else {
    var type=smGetCookie(id+"-type"), idx=smGetCookie(id+"-idx");

    if (mode==0 && type!="" && idx!="") {
      options=smGetList(id, type);
      smSetList(pool.list, options, parseInt(idx,10));
      return;
    }

    smSetList(pool.list, options, pool.top_idx);
    smSetCookie(id+"-type", "top"); smSetCookie(id+"-idx", pool.top_idx);
  }
}

function smSetSubList(id, sub) {
  var pool=smListPool[id], options=smGetList(id, sub);
  smSetList(pool.list, options, pool.item_pool[sub].idx);
  smSetCookie(id+"-type", sub); smSetCookie(id+"-idx", pool.item_pool[sub].idx);
}

function _setCookie(name, value) {
  document.cookie=name+"="+value;
}
function smSetCookie(name, value) {
  setTimeout("_setCookie('"+name+"','"+value+"')",0);
}

function smGetCookie(name) {
  var cookieRE=new RegExp(name+"=([^;]+)");
  if (document.cookie.search(cookieRE)!=-1) {
    return RegExp.$1;
  }
  else {
    return "";
  }
}

// ------
function addList(id, non_item_tag, sub_tag, back_tag) {
  smListPool[id+""]=new smListOBJ(id+"", non_item_tag, sub_tag, back_tag);
}

function addItem(id, num, value, desc) {
  if (smListPool[id+""]) {
    smListPool[id+""].item_pool[num+""]=new smOptionItemOBJ(id+"", num+"", value, desc||value);
  }
}

function addSubList(id, num, idx, desc, item_list) {
  if (smListPool[id+""]) {
    smListPool[id+""].item_pool[num+""]=new smSubListItemOBJ(id+"", num+"", idx-1, desc, item_list);
  }
}

function addTopList(id, idx, item_list) {
  if (smListPool[id+""]) {
    smListPool[id+""].top_idx=idx-1;
    smListPool[id+""].top_list=smGetItems(item_list);
  }
}

function initList(id,list) {
  if (smListPool[id+""] && list) {
    smListPool[id+""].list=list;

    list.list=id+"";
    list.onchange=smUpdateList;

    smEmptyList(id+"");
    smSetTopList(id+"",0);
  }
}

function resetList(id) {
  smEmptyList(id+"");
  smSetTopList(id+"", 1);
}

function checkList(id) {

  var list=smListPool[id+""];
  var $list = $(list.list);
  var value= $list.data('value')+"";

  return (value!=list.back_tag && value!=list.non_item_tag && value.substring(0,list.sub_tag.length)!=list.sub_tag);
}
