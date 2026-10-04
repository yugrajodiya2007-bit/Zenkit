/**
 * Zenkit 3D WebGL Engine using Three.js
 * Renders Hero Interactive Gadget + 360-degree Interactive Product Inspector
 */

// 1. Hero 3D Scene Initialization
function initHero3D() {
  const container = document.getElementById('hero-3d-canvas');
  if (!container || typeof THREE === 'undefined') return;

  const width = container.clientWidth || 400;
  const height = container.clientHeight || 340;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
  camera.position.set(0, 1.2, 5.5);

  const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.innerHTML = '';
  container.appendChild(renderer.domElement);

  // Lighting
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.85);
  scene.add(ambientLight);

  const dirLight1 = new THREE.DirectionalLight(0x38bdf8, 2.2);
  dirLight1.position.set(5, 8, 4);
  scene.add(dirLight1);

  const dirLight2 = new THREE.DirectionalLight(0xf59e0b, 1.5);
  dirLight2.position.set(-5, -4, -2);
  scene.add(dirLight2);

  // 3D Futuristic Gadget Model
  const group = new THREE.Group();

  // Floating Core Device
  const coreGeo = new THREE.CylinderGeometry(1.6, 1.6, 0.28, 48);
  const coreMat = new THREE.MeshStandardMaterial({
    color: 0x1e293b,
    metalness: 0.85,
    roughness: 0.2,
  });
  const core = new THREE.Mesh(coreGeo, coreMat);
  group.add(core);

  // Screen / Display Glow Ring
  const screenGeo = new THREE.CylinderGeometry(1.45, 1.45, 0.3, 48);
  const screenMat = new THREE.MeshStandardMaterial({
    color: 0x0284c7,
    emissive: 0x0284c7,
    emissiveIntensity: 0.6,
    roughness: 0.1,
  });
  const screen = new THREE.Mesh(screenGeo, screenMat);
  group.add(screen);

  // Holographic Orbiting Rings
  const ring1Geo = new THREE.TorusGeometry(2.3, 0.04, 16, 100);
  const ring1Mat = new THREE.MeshBasicMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.8 });
  const ring1 = new THREE.Mesh(ring1Geo, ring1Mat);
  ring1.rotation.x = Math.PI / 3;
  group.add(ring1);

  const ring2Geo = new THREE.TorusGeometry(2.6, 0.03, 16, 100);
  const ring2Mat = new THREE.MeshBasicMaterial({ color: 0xf59e0b, transparent: true, opacity: 0.6 });
  const ring2 = new THREE.Mesh(ring2Geo, ring2Mat);
  ring2.rotation.y = Math.PI / 4;
  group.add(ring2);

  // Floating Micro Sensor Spheres
  for (let i = 0; i < 8; i++) {
    const angle = (i / 8) * Math.PI * 2;
    const sphereGeo = new THREE.SphereGeometry(0.08, 16, 16);
    const sphereMat = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      emissive: 0x38bdf8,
      emissiveIntensity: 0.8
    });
    const sphere = new THREE.Mesh(sphereGeo, sphereMat);
    sphere.position.set(Math.cos(angle) * 2.3, Math.sin(angle) * 0.4, Math.sin(angle) * 2.3);
    group.add(sphere);
  }

  scene.add(group);

  // Interactive Mouse Parallax
  let mouseX = 0;
  let mouseY = 0;
  let targetRotationX = 0.35;
  let targetRotationY = 0;

  window.addEventListener('mousemove', (e) => {
    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;
    mouseX = (e.clientX - windowHalfX) * 0.0005;
    mouseY = (e.clientY - windowHalfY) * 0.0005;
  });

  // Animation Loop
  let clock = new THREE.Clock();
  function animate() {
    requestAnimationFrame(animate);
    const delta = clock.getDelta();
    const time = clock.getElapsedTime();

    // Natural Floating Bob
    group.position.y = Math.sin(time * 1.5) * 0.15;

    // Continuous rotation
    targetRotationY += 0.008;
    group.rotation.y += (targetRotationY + mouseX * 2 - group.rotation.y) * 0.05;
    group.rotation.x += (targetRotationX + mouseY * 2 - group.rotation.x) * 0.05;

    ring1.rotation.z += 0.012;
    ring2.rotation.x += 0.009;

    renderer.render(scene, camera);
  }
  animate();

  // Resize handler
  window.addEventListener('resize', () => {
    if (!container) return;
    const newW = container.clientWidth;
    const newH = container.clientHeight;
    camera.aspect = newW / newH;
    camera.updateProjectionMatrix();
    renderer.setSize(newW, newH);
  });
}

// 2. Interactive 3D Product Inspector Modal
let inspectScene, inspectCamera, inspectRenderer, inspectMeshGroup, inspectType = 'laptop';
let isDragging = false, prevMousePos = { x: 0, y: 0 };
let currentWireframe = false;

function openProduct3D(type, productName) {
  const modal = document.getElementById('fk-3d-modal');
  const titleEl = document.getElementById('fk-3d-modal-title');
  if (!modal) return;

  if (titleEl) titleEl.innerText = productName || 'Interactive 3D Product View';
  modal.classList.add('active');

  inspectType = type || 'laptop';
  initInspectScene();
}

function closeProduct3D() {
  const modal = document.getElementById('fk-3d-modal');
  if (modal) modal.classList.remove('active');
}

function initInspectScene() {
  const container = document.getElementById('product-3d-canvas');
  if (!container || typeof THREE === 'undefined') return;

  const width = container.clientWidth || 800;
  const height = container.clientHeight || 460;

  inspectScene = new THREE.Scene();
  inspectScene.background = new THREE.Color(0x0f172a);

  inspectCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
  inspectCamera.position.set(0, 2, 6);

  if (!inspectRenderer) {
    inspectRenderer = new THREE.WebGLRenderer({ antialias: true });
    inspectRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    container.innerHTML = '';
    container.appendChild(inspectRenderer.domElement);
  }
  inspectRenderer.setSize(width, height);

  // Lighting
  const ambLight = new THREE.AmbientLight(0xffffff, 0.9);
  inspectScene.add(ambLight);

  const mainLight = new THREE.DirectionalLight(0xffffff, 2.0);
  mainLight.position.set(6, 10, 6);
  inspectScene.add(mainLight);

  const rimLight = new THREE.DirectionalLight(0x38bdf8, 1.8);
  rimLight.position.set(-6, -4, -4);
  inspectScene.add(rimLight);

  // Floor Grid
  const gridHelper = new THREE.GridHelper(10, 20, 0x334155, 0x1e293b);
  gridHelper.position.y = -1.6;
  inspectScene.add(gridHelper);

  // Build model geometry based on product type
  buildInspectModel(inspectType);

  // Mouse & Touch Orbit Dragging
  const canvasEl = inspectRenderer.domElement;
  canvasEl.onmousedown = (e) => {
    isDragging = true;
    prevMousePos = { x: e.clientX, y: e.clientY };
  };
  window.onmouseup = () => { isDragging = false; };
  canvasEl.onmousemove = (e) => {
    if (!isDragging || !inspectMeshGroup) return;
    const deltaX = e.clientX - prevMousePos.x;
    const deltaY = e.clientY - prevMousePos.y;
    inspectMeshGroup.rotation.y += deltaX * 0.01;
    inspectMeshGroup.rotation.x += deltaY * 0.01;
    prevMousePos = { x: e.clientX, y: e.clientY };
  };

  // Render loop
  function renderInspect() {
    if (document.getElementById('fk-3d-modal').classList.contains('active')) {
      requestAnimationFrame(renderInspect);
      if (!isDragging && inspectMeshGroup) {
        inspectMeshGroup.rotation.y += 0.005;
      }
      inspectRenderer.render(inspectScene, inspectCamera);
    }
  }
  renderInspect();
}

function buildInspectModel(type) {
  if (inspectMeshGroup) {
    inspectScene.remove(inspectMeshGroup);
  }
  inspectMeshGroup = new THREE.Group();

  const standardMat = new THREE.MeshStandardMaterial({
    color: 0x3b82f6,
    metalness: 0.7,
    roughness: 0.25,
    wireframe: currentWireframe
  });

  const darkMat = new THREE.MeshStandardMaterial({
    color: 0x1e293b,
    metalness: 0.85,
    roughness: 0.2,
    wireframe: currentWireframe
  });

  if (type === 'laptop' || type === 'keyboard') {
    // Laptop Base
    const baseGeo = new THREE.BoxGeometry(3.6, 0.14, 2.5);
    const base = new THREE.Mesh(baseGeo, darkMat);
    inspectMeshGroup.add(base);

    // Keyboard Trackpad
    const padGeo = new THREE.BoxGeometry(1.2, 0.02, 0.8);
    const padMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.5 });
    const pad = new THREE.Mesh(padGeo, padMat);
    pad.position.set(0, 0.08, 0.6);
    inspectMeshGroup.add(pad);

    // Laptop Lid / Screen
    const screenGeo = new THREE.BoxGeometry(3.6, 2.4, 0.1);
    const screenMesh = new THREE.Mesh(screenGeo, darkMat);
    screenMesh.position.set(0, 1.15, -1.2);
    screenMesh.rotation.x = -0.15;
    inspectMeshGroup.add(screenMesh);

    // Display Glass
    const glassGeo = new THREE.BoxGeometry(3.4, 2.2, 0.02);
    const glassMat = new THREE.MeshStandardMaterial({
      color: 0x0284c7,
      emissive: 0x0369a1,
      emissiveIntensity: 0.5
    });
    const glass = new THREE.Mesh(glassGeo, glassMat);
    glass.position.set(0, 1.15, -1.14);
    glass.rotation.x = -0.15;
    inspectMeshGroup.add(glass);

  } else if (type === 'buds') {
    // Wireless Earbuds Charging Case
    const caseGeo = new THREE.SphereGeometry(1.6, 32, 24);
    caseGeo.scale(1.2, 0.9, 0.6);
    const caseMesh = new THREE.Mesh(caseGeo, standardMat);
    inspectMeshGroup.add(caseMesh);

    // Floating Earbud 1
    const budGeo = new THREE.CylinderGeometry(0.3, 0.15, 0.9, 16);
    const bud1 = new THREE.Mesh(budGeo, darkMat);
    bud1.position.set(-1.8, 0.8, 0.4);
    bud1.rotation.z = 0.5;
    inspectMeshGroup.add(bud1);

    // Floating Earbud 2
    const bud2 = new THREE.Mesh(budGeo, darkMat);
    bud2.position.set(1.8, 0.8, 0.4);
    bud2.rotation.z = -0.5;
    inspectMeshGroup.add(bud2);

  } else if (type === 'watch') {
    // Watch Case
    const bodyGeo = new THREE.CylinderGeometry(1.5, 1.5, 0.35, 36);
    const body = new THREE.Mesh(bodyGeo, darkMat);
    body.rotation.x = Math.PI / 2;
    inspectMeshGroup.add(body);

    // AMOLED Watchface
    const faceGeo = new THREE.CylinderGeometry(1.35, 1.35, 0.38, 36);
    const faceMat = new THREE.MeshStandardMaterial({
      color: 0x10b981,
      emissive: 0x059669,
      emissiveIntensity: 0.7
    });
    const face = new THREE.Mesh(faceGeo, faceMat);
    face.rotation.x = Math.PI / 2;
    inspectMeshGroup.add(face);

    // Straps
    const strapGeo = new THREE.BoxGeometry(1.2, 3.8, 0.2);
    const strapMat = new THREE.MeshStandardMaterial({ color: 0x475569 });
    const strap = new THREE.Mesh(strapGeo, strapMat);
    strap.position.z = -0.1;
    inspectMeshGroup.add(strap);

  } else if (type === 'mouse') {
    // Gaming Mouse Ergonomic Shell
    const mouseGeo = new THREE.SphereGeometry(1.5, 32, 24);
    mouseGeo.scale(0.8, 0.5, 1.4);
    const mouseMesh = new THREE.Mesh(mouseGeo, darkMat);
    inspectMeshGroup.add(mouseMesh);

    // Scroll Wheel
    const wheelGeo = new THREE.CylinderGeometry(0.2, 0.2, 0.1, 16);
    const wheelMat = new THREE.MeshStandardMaterial({ color: 0x38bdf8, emissive: 0x0284c7 });
    const wheel = new THREE.Mesh(wheelGeo, wheelMat);
    wheel.rotation.z = Math.PI / 2;
    wheel.position.set(0, 0.5, 0.8);
    inspectMeshGroup.add(wheel);

  } else {
    // Smart Lamp / Orb
    const baseGeo = new THREE.CylinderGeometry(1.2, 1.4, 0.3, 32);
    const base = new THREE.Mesh(baseGeo, darkMat);
    base.position.y = -1.2;
    inspectMeshGroup.add(base);

    const stemGeo = new THREE.CylinderGeometry(0.12, 0.12, 2.2, 16);
    const stem = new THREE.Mesh(stemGeo, darkMat);
    inspectMeshGroup.add(stem);

    const haloGeo = new THREE.TorusGeometry(1.1, 0.15, 24, 60);
    const haloMat = new THREE.MeshStandardMaterial({
      color: 0xfef08a,
      emissive: 0xfde047,
      emissiveIntensity: 0.9
    });
    const halo = new THREE.Mesh(haloGeo, haloMat);
    halo.position.set(0, 1.2, 0);
    inspectMeshGroup.add(halo);
  }

  inspectScene.add(inspectMeshGroup);
}

// 3D Controls
function toggle3DWireframe() {
  currentWireframe = !currentWireframe;
  buildInspectModel(inspectType);
}

function set3DColor(hexColor) {
  if (!inspectMeshGroup) return;
  inspectMeshGroup.traverse((child) => {
    if (child.isMesh && child.material) {
      if (child.material.color && !child.material.emissiveIntensity) {
        child.material.color.setHex(hexColor);
      }
    }
  });
}

function reset3DView() {
  if (inspectMeshGroup) {
    inspectMeshGroup.rotation.set(0, 0, 0);
  }
}

// Document Load Listener
document.addEventListener('DOMContentLoaded', () => {
  initHero3D();
});
