<?php 
session_start();

function recursiveList($data)
{

	if(!empty($data))
	{
		foreach($data as $key => $value)
		{
			if(is_array($value))
			{
				echo '<li><a href="javascript:void(0);" class="goDeeper"><span class="rowVal">' . $key . '</span> <span class="totalsContainer">( <span class="totals"></span> )</span>  <div class="actionContainer"> <i class="fa fa-pencil-square-o editBtn"></i> <i class="fa fa-close deleteItem"></i> <i class="fa fa-arrows-v handle"></i></div></a><ul class="hidden">';
					recursiveList($value);
				echo '</ul></li>';
			}
			else 
			{
				echo '<li><a href="javascript:void(0);" class="goDeeper"><span class="rowVal">' . $value . '</span> <span class="totalsContainer"></span> <div class="actionContainer"> <i class="fa fa-pencil-square-o editBtn"></i> <i class="fa fa-close deleteItem"></i> <i class="fa fa-arrows-v handle"></i></div></a></li>';
			}
			
		}
	}
}

$arrayData = json_decode(stripslashes(file_get_contents(dirname(__FILE__) . '/demo.itree')),true);

if($_POST)
{
	file_put_contents('demo.itree',$_POST['data']);
	exit;
}

?>

<!DOCTYPE html>
<html lang="en">
	
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>Infinitree</title>
		<link rel="stylesheet" href="style.css">
		<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css">
		<link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.css">
		<link rel="apple-touch-icon" href="icon.png"/>  
		<link rel="apple-touch-icon" sizes="72x72" href="icon1.png"/>  
		<link rel="apple-touch-icon" sizes="114x114" href="icon1@2x.png"/>  
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
		<script src="jquery.ui.touch-punch.min.js"></script>
	</head>
	
	<body>
		
		<div id="mainContainer">
			<div id="path"> / </div>
			<div id="topBar">
				<a href="javascript:void(0);" id="back"><i class="fa fa-chevron-left"></i> Back</a>
				<a href="javascript:void(0);" id="showActions"><i class="fa fa-list"></i> Edit branches</a>
			</div>
			
			<ul id="mainList">
				<?php recursiveList($arrayData); ?>
			</ul>
			
			<div id="addNew">
				<textarea id="newItem" rows="1" style="resize: none;"></textarea>
				<a href="javascript:void(0);" id="addNewItem">Add branch</a>
			</div>
		</div>
	  
		<div id="dialog-form" title="Edit">
			<input id="dialogEditVal" type="text" />
		</div>
		
		<script type="text/javascript">
			var currentLevel = 0;
			var currentUL = $('#mainList');
			var currentItem = [];  	
			
			updateTotals();
			
			function updatePath()
			{
				var pathVar = ' / ';
				
				if(currentLevel > 0)
				{
					
					$(currentItem).each(function(index,element) {
						pathVar += element.find('.rowVal:first').html() + ' / ';
						
					});
				}
			
				$('#path').html(pathVar);
				
				updateTotals();
			}
			
			function updateTotals()
			{
			  currentUL.children().each(function(index,element) {
			   if($(element).find('ul:first').children().length > 0)
			   {
					$(element).find('.totalsContainer:first').html( '( <span class="totals">' + $(element).find('ul:first').children().length + ' )</span>' );
			   }
			  })
			  
			}

			function disableCurrentSortable()
			{
				$(currentUL).sortable( "destroy" );
			}
			
			function activateCurrentSortable() 
			{
				$( currentUL ).sortable({
					handle: ".handle",
					update: function( event, ui ) {
						saveList();
					}
				});
			}
			
			$('#back').on('click',function() {
				if(currentLevel > 0)
				{
					var lastItem = currentItem.pop();
					
					lastItem.parent().find('ul').first().addClass('hidden');
					
					lastItem.removeClass('hidden');
					lastItem.parent().siblings().removeClass('hidden');
					disableCurrentSortable();
					currentUL = lastItem.parent().parent();
					activateCurrentSortable();
					currentLevel--;
					
					updatePath();
				}
			});
				
			$('#mainList').on('click', '.goDeeper', function() {
				currentItem.push($(this));
				currentLevel++;

				$(this).addClass('hidden');

				$(this).parent().siblings().addClass('hidden');
				
				disableCurrentSortable();
				if($(this).parent().find('ul').length > 0)
				{
					currentUL = $(this).parent().find('ul').first(); 
					currentUL.removeClass('hidden');
				}
				else
				{
					
					$(this).parent().append('<ul></ul>');
					currentUL = $(this).parent().find('ul').first(); 
				}
				
				activateCurrentSortable();
				updatePath();
			});
			
			$('#addNewItem').on('click',function() {
				var newItemVal = $('#newItem').val().split("\n");
				var answer = true;
				
				if(newItemVal.length > 10) {
					var answer = confirm('Gogule sunt mai mult de 10. Esti sigur ?');
				}
				
				if(answer) {
					for(var i in newItemVal) {
						if(newItemVal[i].length > 0)
						{
							$(currentUL).append('<li><a href="javascript:void(0);" class="goDeeper"><span class="rowVal">' + newItemVal[i] + '</span> <span class="totalsContainer"></span> <div class="actionContainer"> <i class="fa fa-pencil-square-o editBtn"></i> <i class="fa fa-arrows-v handle"></i> <i class="fa fa-close deleteItem"></i></div></a></li>');	
						}
					}
				}
				
				//stupid hack to trim the new lines so it goes to line 0 row 0
				var value = $('#newItem').val().replace(/^(\r\n)|(\n)/,'');
				
				$('#newItem').val(value).val('');

				saveList();
			});

			$('#mainList').on('click', '.deleteItem', function(e) {
				e.stopPropagation();
				
				if(confirm('Are you sure ?'))
				{
					$(this).parents('li:first').remove();
				
					saveList();
				}
			});
			
			
			$('#newItem').on('keyup', function(e) {
				 if(e.keyCode == 13){
					$('#addNewItem').trigger('click');
				 }		
			});
		
			
			function parseUL(node) {
				var returnData = "{";
				var k = 0;
				  
				node.children().each(function(index,element) {
					var separator = node.children().length == index + 1 ? '' : ',';
					
					if($(element).find('ul li').length > 0)
					{
					  
					  var elementValue = $(element).find('.rowVal:first').html().trim();     
					  returnData += ' "' + elementValue + '" : ' + parseUL($(element).find('ul:first')) + separator;
					}
					else
					{
					 
					  returnData += ' "' + k + '" : "' + $(element).find('.rowVal').html().trim() + '" ' + separator;
					  k++;
					}
				  });
				  
				returnData += "}";
				return returnData;
			}


			
			function saveList() {
				var parsedList = parseUL($('#mainList'));
				$.ajax({
					method : 'POST',
					data : { 'data' : parsedList }
				});				
			}

			activateCurrentSortable();

			var currentEditedRowVal;
			
			var dialog = $( "#dialog-form" ).dialog({
							autoOpen: false,
							position: { my: "center top", at: "center top"},
							modal: true,
							buttons: {
							"Edit": function() {
								currentEditedRowVal.html($('#dialogEditVal').val());
								$('#dialogEditVal').val('');
								dialog.dialog( "close" );
								saveList();
							},
							Cancel: function() {
								$('#dialogEditVal').val('');
								dialog.dialog( "close" );
							}
							},
							close: function() {
								$('#dialogEditVal').val('');
								dialog.dialog( "close" );
							}
						});
					
			$('#mainList').on('click','.editBtn', function(e) {
				e.stopPropagation();			
				currentEditedRowVal = $(this).parent().parent().find('.rowVal');			
				$('#dialogEditVal').val($(this).parent().parent().find('.rowVal').html());					
				dialog.dialog( "open" );
			});
			
			$('#showActions').on('click',function() {
				$('.actionContainer').toggle();
			});
			
			
		
		</script>
	</body>
</html>



