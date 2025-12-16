// resources/js/components/three-d/ImageCube.tsx

import { ContactShadows, useTexture } from '@react-three/drei';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import React, { useRef } from 'react';
import * as THREE from 'three';

type ImageCubeProps = {
    imageUrl: string; // e.g. "/images/fieo-logo.png"
};

function ImageCubeInner({ imageUrl }: ImageCubeProps) {
    const meshRef = useRef<THREE.Mesh | null>(null);

    // Load texture from the image URL passed as prop
    const texture = useTexture(imageUrl);
    const { mouse } = useThree();

    useFrame((state) => {
        if (!meshRef.current) return;

        const t = state.clock.getElapsedTime();

        // Fix the logo orientation: rotate 45° in the opposite direction
        meshRef.current.rotation.z = -Math.PI / 4;

        // Subtle vertical float
        meshRef.current.position.y = Math.sin(t * 1.2) * 0.08;

        // Idle spin around Y
        meshRef.current.rotation.y += 0.004;

        // Mouse-based tilt
        const targetX = -mouse.y * 0.8;
        const targetY = mouse.x * 1.0;

        meshRef.current.rotation.x +=
            (targetX - meshRef.current.rotation.x) * 0.08;
        meshRef.current.rotation.y +=
            (targetY - meshRef.current.rotation.y) * 0.08;
    });

    // Same material (image) for all 6 faces
    const materials = React.useMemo(
        () =>
            Array(6)
                .fill(null)
                .map(
                    () =>
                        new THREE.MeshStandardMaterial({
                            map: texture,
                            roughness: 0.35,
                            metalness: 0.3,
                        }),
                ),
        [texture],
    );

    return (
        <mesh ref={meshRef} material={materials}>
            <boxGeometry args={[1.3, 1.3, 1.3]} />
        </mesh>
    );
}

export function ImageCubeCanvas({ imageUrl }: ImageCubeProps) {
    return (
        <Canvas
            className="h-full w-full"
            camera={{ position: [0, 0, 4], fov: 40 }}
            dpr={[1, 2]}
        >
            {/* Basic lighting */}
            <ambientLight intensity={0.7} />
            <directionalLight position={[2, 3, 4]} intensity={1.0} />
            <directionalLight position={[-3, -2, -4]} intensity={0.4} />

            {/* The floating, mouse-reactive cube */}
            <ImageCubeInner imageUrl={imageUrl} />

            {/* Soft contact shadow underneath the cube */}
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
