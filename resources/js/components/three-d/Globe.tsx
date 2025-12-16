import { ContactShadows } from '@react-three/drei';
import { Canvas, useFrame } from '@react-three/fiber';
import { useEffect, useRef, useState } from 'react';
import * as THREE from 'three';

export type GlobeProps = {
  textureUrl: string;             // image file path passed by parent
  globeTint?: string;
  atmosphereColor?: string;
  spinSpeed?: number;
  scale?: number;                 // control overall globe size
};

function GlobeInner({
  textureUrl,
  globeTint = '#38bdf8',
  atmosphereColor = '#60a5fa',
  spinSpeed = 0.0025,
  scale = 0.8,                   // slightly larger default
}: GlobeProps) {
  const groupRef = useRef<THREE.Group | null>(null);
  const globeRef = useRef<THREE.Mesh | null>(null);
  const [texture, setTexture] = useState<THREE.Texture | null>(null);

  useEffect(() => {
    let mounted = true;
    const loader = new THREE.TextureLoader();
    loader.load(
      textureUrl,
      (tex) => {
        if (!mounted) return;
        tex.anisotropy = 8;
        setTexture(tex);
      },
      undefined,
      () => {
        if (!mounted) return;
        setTexture(null);
      },
    );
    return () => {
      mounted = false;
    };
  }, [textureUrl]);

  useFrame((state) => {
    if (!groupRef.current || !globeRef.current) return;
    const t = state.clock.getElapsedTime();
    groupRef.current.position.y = Math.sin(t * 1.2) * 0.06;
    globeRef.current.rotation.y += spinSpeed; // auto-rotation only
  });

  return (
    <group ref={groupRef} scale={scale}>
      {/* Core globe */}
      <mesh ref={globeRef} castShadow receiveShadow>
        <sphereGeometry args={[1.1, 64, 64]} />
        <meshStandardMaterial
          map={texture || undefined}
          color={globeTint}
          roughness={0.6}
          metalness={0.1}
        />
      </mesh>

      {/* Soft atmosphere glow */}
      <mesh>
        <sphereGeometry args={[1.25, 64, 64]} />
        <meshBasicMaterial
          color={atmosphereColor}
          transparent
          opacity={0.18}
          side={THREE.BackSide}
        />
      </mesh>
    </group>
  );
}

export function GlobeCanvas(props: GlobeProps) {
  const { textureUrl, globeTint, atmosphereColor, spinSpeed, scale } = props;

  return (
    <Canvas
      className="w-full h-full"
      camera={{ position: [0, 0, 4], fov: 40 }}
      dpr={[1, 2]}
    >
      <ambientLight intensity={0.7} />
      <directionalLight position={[4, 5, 6]} intensity={1.1} castShadow />
      <directionalLight position={[-3, -2, -5]} intensity={0.4} />
      <GlobeInner
        textureUrl={textureUrl}
        globeTint={globeTint}
        atmosphereColor={atmosphereColor}
        spinSpeed={spinSpeed}
        scale={scale}
      />
      <ContactShadows
        position={[0, -1.6, 0]}
        opacity={0.4}
        scale={4}
        blur={3}
        far={4}
      />
    </Canvas>
  );
}
