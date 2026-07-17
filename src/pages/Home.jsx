import React from 'react';
import ShowroomHero from '../components/home/ShowroomHero';
import ProductShowcase from '../components/showcase/ProductShowcase';
import CapabilityMatrix from '../components/home/CapabilityMatrix';
import Methodology from '../components/home/Methodology';
import Consultation from '../components/home/Consultation';
import { products } from '../data/productData';

export default function Home() {
  return (
    <div>
      <ShowroomHero />

      <div id="showcases">
        {products.map((product, index) => (
          <ProductShowcase key={product.id} product={product} index={index} />
        ))}
      </div>

      <div id="capabilities">
        <CapabilityMatrix />
      </div>

      <div id="methodology">
        <Methodology />
      </div>

      <Consultation />
    </div>
  );
}
