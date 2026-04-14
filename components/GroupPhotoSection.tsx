
import React, { useState, useEffect } from 'react';
import { generateSimpleScienceTree } from '../services/geminiService';

const GroupPhotoSection: React.FC = () => {
  const [aiImage, setAiImage] = useState<string | null>(null);

  useEffect(() => {
    const fetchImage = async () => {
      const img = await generateSimpleScienceTree();
      if (img) setAiImage(img);
    };
    fetchImage();
  }, []);

  return (
    <section className="w-full bg-slate-50 pt-12 pb-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="relative group overflow-hidden rounded-[3rem] shadow-2xl min-h-[500px] flex items-center bg-slate-200 animate-fade-in">
          {/* Overlay with subtle branding */}
          <div className="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent z-10" />
          
          <div className="relative z-20 text-white max-w-2xl px-12 md:px-20 py-12">
            <h3 className="text-3xl md:text-5xl font-black mb-6 uppercase tracking-tighter leading-tight">DAYANIŞMA GÜCÜMÜZDÜR.</h3>
            <p className="text-white/90 text-lg md:text-xl font-bold leading-relaxed">
              ODTÜ’lüler olarak nerede olursak olalım mezuniyetten sonra da ortak değerler sistemimiz etrafında bir araya gelerek topluma fayda sağlarız. ODTÜMİST, işte bu ruhu İstanbul’da yaşatır!
            </p>
            <div className="w-24 h-2 bg-odtu-red mt-8 rounded-full" />
          </div>

          <img 
            src={aiImage || "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000"} 
            alt="ODTÜ Bilim Ağacı" 
            className={`absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-[10s] brightness-[0.5] ${!aiImage ? 'opacity-50 blur-sm' : 'opacity-100 blur-0'} transition-all`}
          />
        </div>
      </div>
    </section>
  );
};

export default GroupPhotoSection;
