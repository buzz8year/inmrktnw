
$(window).bind('resize', function() {
	location.reload();
});

if (!window.requestAnimationFrame) {
	window.requestAnimationFrame = (function () {
		return window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame ||
		function (callback, element) {
			window.setTimeout(callback, 1000 / 60)
		}
	})()
}

var camera, scene, renderer, particle;
var mouseX = 0,
    mouseY = 0;
var windowHalfX = window.innerWidth / 2;
var windowHalfY = window.innerHeight / 2;
var container = $('#particles');

container.on("tap", function() {
	remove();
});

//create();
init();
animate();

function init() {
	camera = new THREE.Camera(75, window.innerWidth / window.innerHeight, 1, 3000);
	camera.position.z = 1000;
	scene = new THREE.Scene();
	for (var i = 0; i < 240; i++) {
	if(1) {
			//var color = Math.random() * 0x2A5F8F + 0xcccccc;
			//var color = '0x43E2D8';
			//Math.random() * 0xCFF09E + 0x3B8686
            var items = Array('0x43E2D8', '0x19c9b0', '0x336FA1');
            var color = items[Math.floor(Math.random()*items.length)];

		} else {
			var color = "0x" + o.particles;
		}
		particle = new THREE.Particle(new THREE.ParticleCircleMaterial({
			color: color,
			opacity: 0.8
		}));
		particle.position.x = Math.random() * 2000 - 1000;
		particle.position.y = Math.random() * 2000 - 1000;
		particle.position.z = Math.random() * 2000 - 1000;
		particle.scale.x = particle.scale.y = Math.random() * 10 + 1;
		scene.addObject(particle)
	}
	renderer = new THREE.CanvasRenderer();
	renderer.setSize(window.innerWidth, window.innerHeight);
	container.append(renderer.domElement);
	document.addEventListener('mousemove', onDocumentMouseMove, false);
	document.addEventListener('touchstart', onDocumentTouchStart, false);
	document.addEventListener('touchmove', onDocumentTouchMove, false)
}
function onDocumentMouseMove(event) {
	mouseX = event.clientX - windowHalfX;
	mouseY = event.clientY - windowHalfY
}
function onDocumentTouchStart(event) {
	if (event.touches.length == 1) {
		event.preventDefault();
		mouseX = event.touches[0].pageX - windowHalfX;
		mouseY = event.touches[0].pageY - windowHalfY
	}
}
function onDocumentTouchMove(event) {
	if (event.touches.length == 1) {
		event.preventDefault();
		mouseX = event.touches[0].pageX - windowHalfX;
		mouseY = event.touches[0].pageY - windowHalfY
	}
}
function animate() {
	requestAnimationFrame(animate);
	render()
}
function render() {
	camera.position.x += (mouseX - camera.position.x) * 0.15;
	camera.position.y += (-mouseY - camera.position.y) * 0.15;
	renderer.render(scene, camera)
}
