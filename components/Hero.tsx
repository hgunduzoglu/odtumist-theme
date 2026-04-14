
import React, { useState, useEffect } from 'react';
import { ArrowRight, ChevronRight, ChevronLeft, Heart, Users, Loader2 } from 'lucide-react';
import { ViewState } from '../types';
import { generateHeroCollage, generateMentorGraphic } from '../services/geminiService';

interface HeroProps {
  onNavigate: (view: ViewState, params?: any) => void;
}

const Hero: React.FC<HeroProps> = ({ onNavigate }) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [aiImages, setAiImages] = useState<{ [key: number]: string | null }>({
    1: null,
    3: null
  });
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchImages = async () => {
      try {
        const [collage, mentor] = await Promise.all([
          generateHeroCollage(),
          generateMentorGraphic()
        ]);
        setAiImages({ 1: collage, 3: mentor });
      } catch (err) {
        console.error("Hero images failed to load", err);
      } finally {
        setIsLoading(false);
      }
    };
    fetchImages();
  }, []);

  const SLIDES = [
    {
      id: 1,
      image: aiImages[1] || 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&q=80&w=2000',
      heading: (
        <>
          İSTANBUL'DAKİ<br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-odtu-red to-orange-500">
            ODTÜ'LÜLERİN
          </span><br />
          BULUŞMA NOKTASI
        </>
      ),
      desc: "İstanbul'da yaşayan binlerce ODTÜ'lü olarak iş, bilim, sanat ve girişim dünyalarına uzanan güçlü bir dayanışma ağını hep birlikte yaşatıyoruz.",
      primaryButton: { label: "TANIŞALIM HOCAM!", action: () => onNavigate(ViewState.ABOUT) },
      theme: 'dark'
    },
    {
      id: 2,
      image: 'https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg',
      heading: (
        <>
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-odtu-red to-orange-500">
            BURS VER,
          </span><br />
          YARINLARA NEFES OL
        </>
      ),
      desc: "Burs gönüllüleri arasına katılın, burs verin, maratonda koşun ve ODTÜ öğrencileri için burs toplayın.",
      primaryButton: { label: "BAĞIŞ YAP", action: () => onNavigate(ViewState.SOLIDARITY, { section: 'burs' }), variant: 'red' },
      secondaryButton: { label: "GÖNÜLLÜ OL", action: () => onNavigate(ViewState.CONTACT), variant: 'blue' },
      theme: 'dark'
    },
    {
      id: 3,
      image: aiImages[3] || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=2000',
      heading: (
        <>
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-odtu-red to-orange-500">
            MENTOR OL,
          </span><br />
          TECRÜBENİ PAYLAŞ
        </>
      ),
      desc: "Genç mezunlara ve öğrencilere yol göster, kariyer yolculuklarında onlara ışık tut.",
      primaryButton: { label: "PROGRAMLARI İNCELE", action: () => onNavigate(ViewState.CONTACT) },
      theme: 'dark'
    }
  ];

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % SLIDES.length);
    }, 8000);
    return () => clearInterval(timer);
  }, []);

  const nextSlide = () => setCurrentSlide((prev) => (prev + 1) % SLIDES.length);
  const prevSlide = () => setCurrentSlide((prev) => (prev === 0 ? SLIDES.length - 1 : prev - 1));

  return (
    <div className="relative h-[85vh] w-full overflow-hidden bg-black">
      {isLoading && currentSlide === 0 && !aiImages[1] && (
        <div className="absolute inset-0 z-50 flex flex-col items-center justify-center bg-slate-900 text-white">
          <Loader2 className="animate-spin text-odtu-red mb-4" size={48} />
          <p className="font-bold text-sm uppercase tracking-widest animate-pulse">Hocam, görseller hazırlanıyor...</p>
        </div>
      )}

      {SLIDES.map((slide, index) => (
        <div 
          key={slide.id}
          className={`absolute inset-0 transition-opacity duration-1000 ease-in-out ${
            index === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'
          }`}
        >
          {/* Background Layer */}
          <div className="absolute inset-0">
            <img 
              src={slide.image} 
              alt="ODTUMIST Hero" 
              className={`w-full h-full object-cover scale-105 transition-all duration-700 brightness-[0.5] ${
                index === currentSlide ? 'animate-[pulse_10s_ease-in-out_infinite]' : ''
              }`} 
            />
            
            <div className={`absolute inset-0 bg-gradient-to-r from-black/80 via-black/20 to-transparent`} />
          </div>

          {/* Content Layer */}
          <div className="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
            <div className={`max-w-3xl transition-all duration-1000 delay-300 transform ${
              index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'
            }`}>
              <h1 className="text-4xl md:text-7xl font-extrabold leading-[1.1] mb-6 drop-shadow-2xl text-white uppercase tracking-tighter">
                {slide.heading}
              </h1>
              
              <p className="text-lg md:text-xl mb-10 leading-relaxed max-w-2xl font-medium text-gray-200">
                {slide.desc}
              </p>
              
              <div className="flex flex-wrap gap-4">
                <button 
                  onClick={slide.primaryButton.action}
                  className="group flex items-center justify-center gap-3 px-8 py-4 bg-odtu-red text-white rounded-full font-bold text-lg transition-all hover:bg-white hover:text-odtu-red hover:shadow-[0_0_20px_rgba(227,30,36,0.5)] border-2 border-transparent hover:border-odtu-red uppercase tracking-wider"
                >
                  {index === 1 && <Heart size={20} className="fill-current" />}
                  {slide.primaryButton.label}
                  <ArrowRight className="group-hover:translate-x-1 transition-transform" />
                </button>

                {slide.secondaryButton && (
                  <button 
                    onClick={slide.secondaryButton.action}
                    className="group flex items-center justify-center gap-3 px-8 py-4 bg-odtu-blue text-white rounded-full font-bold text-lg transition-all hover:bg-white hover:text-odtu-blue hover:shadow-[0_0_20px_rgba(0,82,155,0.4)] border-2 border-transparent hover:border-odtu-blue uppercase tracking-wider"
                  >
                    <Users size={20} />
                    {slide.secondaryButton.label}
                    <ArrowRight className="group-hover:translate-x-1 transition-transform" />
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>
      ))}

      {/* Navigation Arrows */}
      <div className="absolute bottom-10 right-10 z-20 flex gap-4">
        <button 
          onClick={prevSlide} 
          className="p-4 rounded-full border border-white/20 text-white hover:bg-white hover:text-black transition-all backdrop-blur-sm group"
          aria-label="Önceki Slayt"
        >
          <ChevronLeft size={24} className="group-hover:-translate-x-1 transition-transform" />
        </button>
        <button 
          onClick={nextSlide} 
          className="p-4 rounded-full border border-white/20 text-white hover:bg-white hover:text-black transition-all backdrop-blur-sm group"
          aria-label="Sonraki Slayt"
        >
          <ChevronRight size={24} className="group-hover:translate-x-1 transition-transform" />
        </button>
      </div>

      {/* Progress Indicators */}
      <div className="absolute bottom-10 left-10 z-20 flex gap-2">
        {SLIDES.map((_, idx) => (
          <div 
            key={idx}
            className={`h-1.5 transition-all duration-500 rounded-full ${
              idx === currentSlide ? 'w-12 bg-odtu-red' : 'w-4 bg-white/30'
            }`}
          />
        ))}
      </div>
    </div>
  );
};

export default Hero;
