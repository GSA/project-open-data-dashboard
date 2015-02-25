// Raphael donut example courtesy http://codepen.io/dshapira/details/CJind/

var pheight = parseInt($container.css('height')),
    pwidth  = parseInt($container.css('width')),
    radius  = pwidth < pheight ? pwidth/3 : pheight/3;
    bgcolor = jQuery('body').css('background-color');

var paper = new Raphael($container[0], pwidth, pheight);

// draw the piechart
var pie = paper.piechart(pwidth/2, pheight/2, radius, data, { 
  legend: lengendlabels, 
  legendpos: 'east',
  legendcolor: '#666666',
  stroke: bgcolor,
  strokewidth: 1,
  colors: barcolors
});

// assign the hover in/out functions
pie.hover(function () {
  this.sector.stop();
  this.sector.scale(1.1, 1.1, this.cx, this.cy);
  this.sector.animate({ 'stroke': highlightcolor }, 400);
  this.sector.animate({ 'stroke-width': 1 }, 500, "bounce");
  
  if (this.label) {
    this.label[0].stop();
    this.label[0].attr({ r: 8.5 });
    this.label[1].attr({ "font-weight": 800 });
    center_label.attr('text', this.value.value + '%');
    center_label.animate({ 'opacity': 1.0 }, 200);
  }
  }, function () {
    this.sector.animate({ transform: 's1 1 ' + this.cx + ' ' + this.cy }, 500, "bounce");
    this.sector.animate({ 'stroke': bgcolor }, 400);
    if (this.label) {
      this.label[0].animate({ r: 5 }, 500, "bounce");
      this.label[1].attr({ "font-weight": 400 });
      //center_label.attr('text','');
      center_label.animate({ 'opacity': 0.5 }, 500);
    }
});

// blank circle in center to create donut hole effect
paper.circle(pwidth/2, pheight/2, radius*0.6)
  .attr({'fill': bgcolor, 'stroke': bgcolor});

var center_label = paper.text(pwidth/2, pheight/2, '')
  .attr({'fill': '#666666', 'font-size': '18', "font-weight": 800, 'opacity': 0.0 });