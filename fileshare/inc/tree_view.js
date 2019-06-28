// JavaScript Document
// <script language="JavaScript1.2" type="text/JavaScript">
// Copyright (c)2005 Rewritten Software.  http://www.rewrittensoftware.com
// This script is supplied "as is" witrhout any form of warranty. Rewritten Software 
// shall not be liable for any loss or damage to person or property as a result of using this script.
// Use this script at your own risk!
// You are licensed to use this script free of charge for commercial or non-commercial use providing you do not remove 
// the copyright notice or disclaimer.

// Define the array that will contain the mapping table for ids to images.
var iconMap = new Array();
var iconList = new Array( iconMap );

function Toggle(item)
{
	var idx = -1;
	for( i = 0; i < iconList.length; i++ )
	{
		if( iconList[i][0] == item )
		{
			idx = i;
			break;
		}
	}
	
	if( idx < 0 )
		alert( "Could not find key in Icon List." );
		   
	var div=document.getElementById("D"+item);
	var visible=(div.style.display!="none");
	var key=document.getElementById("P"+item);
	
	
	// Check if the item clicked has any children. If it does not then remove the plus/minus icon
	// and replace it with a transaparent gif.
	var removeIcon = div.hasChildNodes() == false;
	
	if( key != null )
	{
		if( !removeIcon )
		{
			if (visible)
			{
		 		div.style.display="none";
		 		key.innerHTML="<img src='../images/tv_plus.gif' width='16' height='16' hspace='0' vspace='0' border='0'>";
			}
			else
			{
		  		div.style.display="block";
				key.innerHTML="<img src='../images/tv_minus.gif' width='16' height='16' hspace='0' vspace='0' border='0'>";
			}
		}
		else
			key.innerHTML="<img src='../images/tv_transparent.gif' width='16' height='16' hspace='0' vspace='0' border='0'>";
	}

	// Toggle the icon for the tree item
	key=document.getElementById("I"+item);
	if( key != null )
	{
		if (visible)
		{
	 		div.style.display="none";
	 		key.innerHTML="<img src='"+iconList[idx][1]+"' width='16' height='16' hspace='0' vspace='0' border='0'>";
		}
		else
		{
	  		div.style.display="block";
			key.innerHTML="<img src='"+iconList[idx][2]+"' width='16' height='16' hspace='0' vspace='0' border='0'>";
		}
	}	
}

function Expand() {
   divs=document.getElementsByTagName("DIV");
   for (i=0;i<divs.length;i++) {
		 divs[i].style.display="block";
		 key=document.getElementById("x" + divs[i].id);
		 key.innerHTML="<img src='../images/tv_folder.gif' width='16' height='16' hspace='0' vspace='0' border='0'>";
   }
}

function Collapse() {
   divs=document.getElementsByTagName("DIV");
   for (i=0;i<divs.length;i++) {
		 divs[i].style.display="none";
		 key=document.getElementById("x" + divs[i].id);
		 key.innerHTML="<img src='../images/tv_folder.gif' width='16' height='16' hspace='0' vspace='0' border='0'>";
   }
}

function AddImage( parent, imgFileName )
{
	img=document.createElement("IMG");
	img.setAttribute( "src", imgFileName );
	img.setAttribute( "width", 16 );
	img.setAttribute( "height", 16 );
	img.setAttribute( "hspace", 0 );
	img.setAttribute( "vspace", 0 );
	img.setAttribute( "border", 0 );
	parent.appendChild(img);
}

function CreateUniqueTagName( seed )
{
	var tagName = seed;
	var attempt = 0;
	
	if( tagName == "" || tagName == null )
		tagName = "x";

	while( document.getElementById(tagName) != null )
	{
		tagName = "x" + tagName;
		if( attempt++ > 50 )
		{
			alert( "Cannot create unique tag name. Giving up. \nTag = " + tagName );
			break;
		}
	}
	
	return tagName;
}

// Creates a new package under a parent. 
// Returns a TABLE tag to place child elements under.
function CreateTreeItem( parent, img1FileName, img2FileName, nodeName, url, target, fontWeight )
{
	var uniqueId = CreateUniqueTagName( nodeName );
	for( i=0; i < iconList.length; i++ )
		if( iconList[i][0] == uniqueId )
		{
			alert( "Non unique ID in Element Map. '" + uniqueId + "'" );
			// return;
		}
	iconList[iconList.length] = new Array( uniqueId, img1FileName, img2FileName );

	table = document.createElement("TABLE");
	if( parent != null )
		parent.appendChild( table );

	table.setAttribute( "border", 0 );
	table.setAttribute( "cellpadding", 1 );
	table.setAttribute( "cellspacing", 1 );
		
	tablebody = document.createElement("TBODY");
	table.appendChild(tablebody);
		
   	row=document.createElement("TR");
	tablebody.appendChild( row );

	// Create the cell for the plus and minus.
	cell=document.createElement("TD");
	cell.setAttribute( "width", 16 );
	row.appendChild(cell);
	
		// Create the hyperlink for plus/minus the cell
	a=document.createElement("A");
	cell.appendChild( a );
	a.setAttribute( "id", "P"+uniqueId );
	a.setAttribute( "href", "javascript:Toggle(\""+uniqueId+"\");" );
	AddImage( a, "../images/tv_plus.gif" );
	
	// Create the cell for the image.
	cell=document.createElement("TD");
	cell.setAttribute( "width", 16 );
	row.appendChild(cell);
		
	// all the event to call when the icon is clicked.
	a=document.createElement("A");
	a.setAttribute( "id", "I"+uniqueId );
	a.setAttribute( "href", "javascript:Toggle(\""+uniqueId+"\");" );
	cell.appendChild(a);

	// Add the image to the cell
	AddImage( a, img1FileName );

	// Create the cell for the text
	cell=document.createElement("TD");
	cell.noWrap = true;
	a=document.createElement("A");
	a.setAttribute( "id", uniqueId );
	cell.appendChild( a );
	if( url != null )
	{
		a.setAttribute( "href", url );
		if( target != null )
			a.setAttribute( "target", target );
		else
			a.setAttribute( "target", "_blank" );

		if(fontWeight == 1) a.setAttribute( "style", "font-weight:900" );
		
		text=document.createTextNode( nodeName );
		a.appendChild(text);
	}
	else
	{
		text=document.createTextNode( nodeName );
		cell.appendChild(text);
	}
	row.appendChild(cell);

	return CreateDiv( parent, uniqueId );;
}

// Creates a new DIV tag and appends it to parent if parent is not null.
// Returns the new DIV tag.
function CreateDiv( parent, id )
{
	div=document.createElement("DIV");
	if( parent != null )
		parent.appendChild( div );
		
	div.setAttribute( "id", "D"+id );
	div.style.display  = "none";
 	div.style.marginLeft = "1em";
	
	return div;
}

// This is the root of the tree. It must be supplied as the parent for anything at the top level of the tree.
var rootCell = null;

// This is the entry method into the Tree View. It builds an initial single row, single cell table tat will 
// contain the tree. It initialises a global object "rootCell". This object must be used as the parent for all 
// top-level tree elements.
// There are two methods for creating tree elements: CreatePackage() and CreateNode(). The images for the 
// package are hard coded. CreateNode() allows you to supply your own image for each node element.
function Initialise()
{
	//body = document.getElementsByTagName("body").item(0);
	//body.setAttribute( "leftmargin", 2 );
	//body.setAttribute( "topmargin", 0 );
	//body.setAttribute( "marginwidth", 0 );
	//body.setAttribute( "marginheight", 0 );
	body = document.getElementById("treeview");
	
	table = document.createElement("TABLE");
	body.appendChild( table );

	table.setAttribute( "border", 0 );
	table.setAttribute( "cellpadding", 1 );
	table.setAttribute( "cellspacing", 1 );
		
	tablebody = document.createElement("TBODY");
	table.appendChild(tablebody);
		
	row=document.createElement("TR");
	tablebody.appendChild(row);
		
	cell=document.createElement("TD");
	row.appendChild(cell); 	
	
	rootCell = cell;	// Initialise the root of the tree view.
}
