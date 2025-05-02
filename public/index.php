<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>y | express yourself</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Outfit font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1C9BEF;
            --secondary: #7856FF;
            --accent: #FF2C55;
            --dark: #0A1419;
            --light: #F7F9FA;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            text-transform: lowercase;
        }
        
        body {
            min-height: 100vh;
            background-color: var(--dark);
            overflow: hidden;
            position: relative;
            color: var(--light);
        }
        
        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(
                ellipse at center,
                #0d1c28 0%,
                #091520 45%,
                #06101a 100%
            );
            z-index: -2;
        }
        
        /* Particles container */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        /* Simple centered content */
        .content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .logo {
            font-size: 14rem;
            font-weight: 700;
            color: var(--light);
            margin-bottom: 1.5rem;
        }
        
        /* Button styling */
        .enter-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 3.5rem;
            font-size: 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(28, 155, 239, 0.3);
            opacity: 0;
            animation: fadeIn 1s forwards 0.5s;
        }
        
        .enter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(28, 155, 239, 0.5);
        }
        
        .enter-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(28, 155, 239, 0.3);
        }
        
        .enter-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: skewX(-30deg);
            transition: 0.5s;
        }
        
        .enter-btn:hover::before {
            left: 100%;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="particles" id="particles"></div>
    
    <div class="content">
        <div class="logo">y</div>
        <button id="enter-btn" class="enter-btn">enter y</button>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const particlesContainer = document.getElementById('particles');
            const enterBtn = document.getElementById('enter-btn');
            let animationFrameId; // Store animation frame ID
            
            // Button click event
            enterBtn.addEventListener('click', () => {
                // Add a small animation effect before redirecting
                enterBtn.style.transition = 'all 0.3s';
                enterBtn.style.transform = 'scale(0.95)';
                enterBtn.style.opacity = '0.8';
                
                setTimeout(() => {
                    window.location.href = '/y/public/app/feed.php';
                }, 200);
            });
            
            // Create floating particles
            function createParticles() {
                const particleCount = window.innerWidth > 768 ? 100 : 50;
                
                for (let i = 0; i < particleCount; i++) {
                    const size = Math.random() * 3 + 1;
                    const particle = document.createElement('span');
                    
                    // Random position
                    const posX = Math.random() * 100;
                    const posY = Math.random() * 100;
                    
                    // Random speed
                    const speedX = (Math.random() - 0.5) * 0.3;
                    const speedY = (Math.random() - 0.5) * 0.3;
                    
                    // Apply styles
                    particle.style.position = 'absolute';
                    particle.style.display = 'block';
                    particle.style.pointerEvents = 'none';
                    particle.style.opacity = '0.4';
                    particle.style.background = 'white';
                    particle.style.borderRadius = '50%';
                    particle.style.boxShadow = '0 0 10px rgba(255, 255, 255, 0.5)';
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${posX}%`;
                    particle.style.top = `${posY}%`;
                    
                    // Store speed as data attributes
                    particle.dataset.speedX = speedX;
                    particle.dataset.speedY = speedY;
                    
                    particlesContainer.appendChild(particle);
                }
            }
            
            function animateParticles() {
                const particles = particlesContainer.children;
                
                for (let particle of particles) {
                    let posX = parseFloat(particle.style.left);
                    let posY = parseFloat(particle.style.top);
                    
                    const speedX = parseFloat(particle.dataset.speedX);
                    const speedY = parseFloat(particle.dataset.speedY);
                    
                    // Update position
                    posX += speedX;
                    posY += speedY;
                    
                    // Wrap around screen edges
                    if (posX > 100) posX = 0;
                    if (posX < 0) posX = 100;
                    if (posY > 100) posY = 0;
                    if (posY < 0) posY = 100;
                    
                    particle.style.left = `${posX}%`;
                    particle.style.top = `${posY}%`;
                }
                
                // Store the animation frame ID so we can cancel it later
                animationFrameId = requestAnimationFrame(animateParticles);
            }
            
            // Create stars (smaller, non-moving particles)
            function createStars() {
                const starCount = window.innerWidth > 768 ? 80 : 40;
                
                for (let i = 0; i < starCount; i++) {
                    const size = Math.random() * 2 + 0.5;
                    const star = document.createElement('div');
                    
                    const posX = Math.random() * 100;
                    const posY = Math.random() * 100;
                    const opacity = Math.random() * 0.5 + 0.3;
                    
                    star.style.position = 'absolute';
                    star.style.width = `${size}px`;
                    star.style.height = `${size}px`;
                    star.style.left = `${posX}%`;
                    star.style.top = `${posY}%`;
                    star.style.backgroundColor = 'white';
                    star.style.borderRadius = '50%';
                    star.style.opacity = opacity;
                    
                    // Add subtle twinkle animation
                    star.style.animation = `twinkle ${Math.random() * 3 + 2}s infinite alternate ease-in-out`;
                    star.style.animationDelay = `${Math.random() * 5}s`;
                    
                    particlesContainer.appendChild(star);
                }
            }
            
            // Define the twinkle animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes twinkle {
                    0%, 100% { opacity: 0.6; transform: scale(1); }
                    50% { opacity: 0.3; transform: scale(0.7); }
                }
            `;
            document.head.appendChild(style);
            
            // Initialize particles and start animation
            function initParticles() {
                // Cancel any existing animation frame before creating new one
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                }
                
                // Clear container
                particlesContainer.innerHTML = '';
                
                // Create new particles and stars
                createParticles();
                createStars();
                
                // Start animation
                animateParticles();
            }
            
            // Initialize particles on load
            initParticles();
            
            // Handle window resize with debounce to prevent too many redraws
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    initParticles();
                }, 250);
            });
        });
    </script>
</body>
</html>