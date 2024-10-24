const video = document.getElementById('videoElement');
const canvas = document.getElementById('visualizer');
const playButton = document.getElementById('playButton');
const ctx = canvas.getContext('2d');

let audioContext;
let analyzer;
let source;

// Set canvas size to match video dimensions
canvas.width = video.offsetWidth;
canvas.height = video.offsetHeight;

function initAudio() {
	audioContext = new (window.AudioContext || window.webkitAudioContext)();
	analyzer = audioContext.createAnalyser();
	analyzer.fftSize = 256;
	
	source = audioContext.createMediaElementSource(video);
	source.connect(analyzer);
	analyzer.connect(audioContext.destination);
}

const bufferLength = 128;
const dataArray = new Uint8Array(bufferLength);

let swirlingCircle = {
	angle: 0,
	radius: 0,
	opacity: 0,
	pulseDuration: 3000, // Initial pulse duration (3 seconds)
	lastPulseTime: 0
};

function drawSwirlingCircle(centerX, centerY, maxRadius) {
	const currentTime = Date.now();
	if (currentTime - swirlingCircle.lastPulseTime > swirlingCircle.pulseDuration) {
		swirlingCircle.lastPulseTime = currentTime;
		swirlingCircle.pulseDuration = Math.random() * 2000 + 2000; // Random duration between 2-4 seconds
	}
	
	const pulseProgress = (currentTime - swirlingCircle.lastPulseTime) / swirlingCircle.pulseDuration;
	const pulseFactor = Math.sin(pulseProgress * Math.PI);
	
	swirlingCircle.angle += 0.02;
	swirlingCircle.radius = maxRadius * 0.3 * (1 + 0.1 * pulseFactor);
	
	const x = centerX + Math.cos(swirlingCircle.angle) * swirlingCircle.radius;
	const y = centerY + Math.sin(swirlingCircle.angle) * swirlingCircle.radius;
	
	ctx.beginPath();
	ctx.arc(x, y, 10, 0, 2 * Math.PI);
	ctx.fillStyle = `rgba(255, 255, 255, ${swirlingCircle.opacity})`;
	ctx.fill();
}


let rotations = [0, 0, 0];
let startAngles = [0, Math.PI / 3, 2 * Math.PI / 3];
let rotationSpeeds = [0.004, 0.009, 0.007];

function animate() {
	requestAnimationFrame(animate);
	
	// Clear canvas
	ctx.clearRect(0, 0, canvas.width, canvas.height);
	
	// Get frequency data
	analyzer.getByteFrequencyData(dataArray);
	
	const centerX = canvas.width / 2;
	const centerY = canvas.height / 2;
	const maxRadius = Math.min(centerX, centerY) * 0.8;
	
	// Draw multiple circles
	for (let j = 0; j < 3; j++) {
		ctx.save();
		ctx.translate(centerX, centerY);
		ctx.rotate(rotations[j]);
		ctx.translate(-centerX, -centerY);
		
		ctx.beginPath();
		ctx.strokeStyle = `rgba(0, 128, 255, ${0.3 + j * 0.2})`;
		ctx.lineWidth = 2 + (j*2);
		
		for (let i = 0; i <= bufferLength; i++) {
			const angle = (i / bufferLength) * 2 * Math.PI + startAngles[j];
			let value = dataArray[i] || 0;
			
			// Add constant low-intensity oscillation
			const baseOscillation = Math.sin(Date.now() / 1000 + i) * 50;
			value = Math.max(value, baseOscillation);
			
			const offset = (value / 255) * (maxRadius * 0.1);
			const radius = maxRadius - (j * maxRadius / 20);
			
			const x = centerX + Math.cos(angle) * (radius + offset);
			const y = centerY + Math.sin(angle) * (radius + offset);
			
			if (i === 0) {
				ctx.moveTo(x, y);
			} else {
				ctx.lineTo(x, y);
			}
		}
		
		ctx.closePath();
		
		// Add a glow effect
		ctx.shadowBlur = 15;
		ctx.shadowColor = 'rgba(0, 128, 255, 0.5)';
		ctx.stroke();
		
		// Reset shadow
		ctx.shadowBlur = 0;
		
		ctx.restore();
		
		// Increment rotation
		rotations[j] += rotationSpeeds[j];
	}
	
	// Draw swirling circle
	drawSwirlingCircle(centerX, centerY, maxRadius);
}
playButton.addEventListener('click', () => {
	if (!audioContext) {
		initAudio();
	}
	if (video.paused) {
		video.play();
		animate();
	} else
	{
		video.pause();
	}
});

video.addEventListener('pause', () => {
	if (audioContext && audioContext.state === 'running') {
		audioContext.suspend();
	}
});

video.addEventListener('play', () => {
	if (audioContext && audioContext.state === 'suspended') {
		audioContext.resume();
	}
});
