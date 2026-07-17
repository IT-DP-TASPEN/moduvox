import React, { useState, useEffect, useRef } from 'react';
import ShowroomHero from '../components/home/ShowroomHero';
import ProductShowcase from '../components/showcase/ProductShowcase';
import CapabilityMatrix from '../components/home/CapabilityMatrix';
import Methodology from '../components/home/Methodology';
import Consultation from '../components/home/Consultation';
import { products } from '../data/productData';

// Wave Divider Component
function WaveDivider({ fromColor = '#FFFFFF', toColor = '#F1F5F9', flipped = false }) {
  return (
    <div className="section-divider" style={flipped ? { transform: 'rotate(180deg)' } : {}}>
      <svg viewBox="0 0 1440 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C360,48 1080,48 1440,0 L1440,48 L0,48 Z" fill={toColor} />
      </svg>
    </div>
  );
}

export default function Home() {
  const [activeSection, setActiveSection] = useState(0);
  const sectionRefs = useRef([]);

  // Map product IDs for dot nav
  const navItems = products.map(p => ({ id: p.id, label: p.name }));

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY + window.innerHeight / 2;
      
      for (let i = sectionRefs.current.length - 1; i >= 0; i--) {
        const el = sectionRefs.current[i];
        if (el && el.offsetTop <= scrollY) {
          setActiveSection(i);
          break;
        }
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const scrollToSection = (index) => {
    const el = sectionRefs.current[index];
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  return (
    <div>
      <ShowroomHero />

      {/* === DOT NAVIGATION (Side) === */}
      <nav className="dot-nav" aria-label="Section navigation">
        {navItems.map((item, i) => (
          <button
            key={item.id}
            className={`dot-nav-item ${activeSection === i ? 'active' : ''}`}
            onClick={() => scrollToSection(i)}
            aria-label={item.label}
          >
            <span className="dot-tooltip">{item.label}</span>
          </button>
        ))}
      </nav>

      {/* === PRODUCT SHOWCASES === */}
      <div id="showcases">
        {products.map((product, index) => (
          <div
            key={product.id}
            ref={el => sectionRefs.current[index] = el}
          >
            <ProductShowcase product={product} index={index} />
          </div>
        ))}
      </div>

      {/* === DIVIDER: Showcases → Capabilities === */}
      <WaveDivider fromColor="#F1F5F9" toColor="#FFFFFF" flipped />

      <div id="capabilities">
        <CapabilityMatrix />
      </div>

      {/* === DIVIDER: Capabilities → Methodology === */}
      <WaveDivider fromColor="#FFFFFF" toColor="#F8FAFC" />

      <div id="methodology">
        <Methodology />
      </div>

      <Consultation />
    </div>
  );
}
