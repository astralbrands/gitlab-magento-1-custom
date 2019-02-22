jQuery(document).click(function(event){

  console.log(event);

  if(event.target.className === 'viewReviews'){
  event.preventDefault();
  viewReviews();
  }
  if(event.target.className === 'addReview'){
  event.preventDefault();
  addReview();
  }
});

function viewReviews(){
jQuery('.review-block-reviews').dialog({
dialogClass: 'noTitleBar',
minHeight: 100,
minWidth: 390,
maxWidth: 800,
draggable: true,
resizable: false,
show: {
effect: "fade",
duration: 1000
},
hide: {
effect: "fade",
duration: 500
}
});
}

function addReview(){
jQuery('.review-block-form').dialog({
dialogClass: 'noTitleBar',
minHeight: 100,
minWidth: 390,
maxWidth: 800,
draggable: true,
resizable: false,
show: {
effect: "fade",
duration: 1000
},
hide: {
effect: "fade",
duration: 500
}
});
}