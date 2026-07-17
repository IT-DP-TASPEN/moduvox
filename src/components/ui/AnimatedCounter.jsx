import React, { useEffect, useRef, useState } from 'react';
import { motion, useInView } from 'framer-motion';

export default function AnimatedCounter({ value, prefix = '', suffix = '', displayValue, duration = 1.5 }) {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-50px' });
  const [count, setCount] = useState(0);

  useEffect(() => {
    if (!isInView || displayValue) return;

    let start = 0;
    const end = typeof value === 'number' ? value : parseFloat(value);
    if (isNaN(end)) return;

    const stepTime = (duration * 1000) / 60;
    const increment = end / (duration * 60);
    let current = 0;

    const timer = setInterval(() => {
      current += increment;
      if (current >= end) {
        setCount(end);
        clearInterval(timer);
      } else {
        setCount(current);
      }
    }, stepTime);

    return () => clearInterval(timer);
  }, [isInView, value, duration, displayValue]);

  const formatNumber = (num) => {
    if (Number.isInteger(num) || num > 100) return Math.round(num).toLocaleString('id-ID');
    return num.toFixed(1);
  };

  return (
    <motion.span
      ref={ref}
      initial={{ opacity: 0, y: 10 }}
      animate={isInView ? { opacity: 1, y: 0 } : {}}
      transition={{ duration: 0.5 }}
      style={{ fontVariantNumeric: 'tabular-nums' }}
    >
      {displayValue ? displayValue : `${prefix}${formatNumber(count)}${suffix}`}
    </motion.span>
  );
}
