// resources/js/components/three-d/TextCube.tsx

import React, { useMemo, useRef } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { ContactShadows, RoundedBox } from '@react-three/drei';
import * as THREE from 'three';

type TextCubeProps = {
  text: string;
  backgroundColor?: string;
  textColor?: string;
};

function createTextTexture(
  text: string,
  backgroundColor: string,
  textColor: string,
): THREE.CanvasTexture {
  const size = 1024;
  const canvas = document.createElement('canvas');
  canvas.width = size;
  canvas.height = size;
  const ctx = canvas.getContext('2d')!;
  ctx.fillStyle = backgroundColor;
  ctx.fillRect(0, 0, size, size);

  ctx.fillStyle = textColor;
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  const fontSize = size * 0.18;
  ctx.font = `bold ${fontSize}px Inter, system-ui, sans-serif`;
  ctx.fillText(text, size / 2, size / 2);

  const texture = new THREE.CanvasTexture(canvas);
  texture.anisotropy = 8;
  texture.needsUpdate = true;
  return texture;
}

function TextCubeInner({
  text,
  backgroundColor = '#0f172a',
  textColor = '#38bdf8',
}: TextCubeProps) {
  const meshRef = useRef<THREE.Mesh>(null);
  const { mouse } = useThree();

  const texture = useMemo(
    () => createTextTexture(text, backgroundColor, textColor),
    [text, backgroundColor, textColor],
  );

  useFrame((state) => {
    if (!meshRef.current) return;
    const t = state.clock.getElapsedTime();
    meshRef.current.position.y = Math.sin(t * 1.2) * 0.08;
    meshRef.current.rotation.y += 0.004;
    const targetX = -mouse.y * 0.6;
    const targetY = mouse.x * 0.8;
    meshRef.current.rotation.x += (targetX - meshRef.current.rotation.x) * 0.08;
    meshRef.current.rotation.y += (targetY - meshRef.current.rotation.y) * 0.08;
  });

  const materials = useMemo(
    () =>
      Array(6)
        .fill(null)
        .map(
          () =>
            new THREE.MeshStandardMaterial({
              map: texture,
              roughness: 0.3,
              metalness: 0.35,
            }),
        ),
    [texture],
  );

  return (
    <RoundedBox
      ref={meshRef}
      args={[1.4, 1.4, 1.4]}
      radius={0.12}      // rounded edges
      smoothness={6}     // smooth curvature
      material={materials as any}
    />
  );
}

export function TextCubeCanvas({
  text,
  backgroundColor,
  textColor,
}: TextCubeProps) {
  return (
    <Canvas
      className="w-full h-full"
      camera={{ position: [0, 0, 4], fov: 40 }}
      dpr={[1, 2]}
    >
      <ambientLight intensity={0.9} />
      <directionalLight position={[2, 3, 4]} intensity={1.1} />
      <directionalLight position={[-3, -2, -4]} intensity={0.4} />
      <TextCubeInner
        text={text}
        backgroundColor={backgroundColor}
        textColor={textColor}
      />
      <ContactShadows
        position={[0, -1.4, 0]}
        opacity={0.35}
        scale={4}
        blur={2.4}
        far={4}
      />
    </Canvas>
  );
}
