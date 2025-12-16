// resources/js/components/FieoLogo.tsx

import React from 'react';

type FieoLogoProps = {
  width?: number | string; // accepts number (pixels) or Tailwind-like string (e.g. '50%')
  className?: string;      // optional extra classes
};

export default function FieoLogo({ width = 120, className = '' }: FieoLogoProps) {
  return (
    <img
      src="/images/fieo-logo-trans.svg"
      alt="FIEO Logo"
      width={typeof width === 'number' ? width : undefined}
      style={typeof width === 'string' ? { width } : undefined}
      className={['object-contain select-none', className].join(' ')}
      loading="lazy"
      draggable={false}
    />
  );
}
