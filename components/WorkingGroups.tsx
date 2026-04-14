import React, { useRef, useEffect, useState } from 'react';
import { WORKING_GROUPS } from '../constants';
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-react';

const WorkingGroups: React.FC = () => {
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  // Sonsuz döngü illüzyonu için listeyi üçlüyoruz
  const tripledGroups = [...WORKING_GROUPS, ...WORKING_GROUPS, ...WORKING_GROUPS];
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    // Başlangıçta orta sete odaklan
    if (scrollContainerRef.current) {
      const singleSetWidth = scrollContainerRef.current.scrollWidth / 3;
      scrollContainerRef.current.scrollLeft = singleSetWidth;
      setIsReady(true);
    }
  }, []);

  const handleScroll = () => {
    if (!scrollContainerRef.current) return;
    const { scrollLeft, scrollWidth } = scrollContainerRef.current;
    const singleSetWidth = scrollWidth / 3;

    // Eğer sol sınırına dayandıysak (ilk setin başına), orta sete atla
    if (scrollLeft <= 0) {
      scrollContainerRef.current.scrollLeft = singleSetWidth;
    } 
    // Eğer sağ sınırına dayandıysak (üçüncü setin sonuna), orta sete atla
    else if (scrollLeft >= singleSetWidth * 2) {
      scrollContainerRef.current.scrollLeft = singleSetWidth;
    }
  };

  const scroll = (direction: 'left' | 'right') => {
    if (scrollContainerRef.current) {
      const cardWidth = 324; // 300px card + 24px gap (gap-6)
      const scrollAmount = direction === 'left' ? -cardWidth : cardWidth;
      scrollContainerRef.current.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
  };

  return (
    <section className="py-24 bg-slate-900 overflow-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <span className="text-odtu-blue font-black uppercase tracking-[0.2em] text-xs">Birlikte Üretiyoruz</span>
            <h2 className="text-4xl md:text-6xl font-black text-white mt-2 uppercase tracking-tighter">Çalışma Gruplarımız</h2>
          </div>
          <div className="flex items-center gap-4">
            <div className="flex gap-2 mr-4">
               <div className="h-1.5 w-20 bg-odtu-red rounded-full" />
               <div className="h-1.5 w-10 bg-odtu-blue rounded-full" />
            </div>
            <div className="flex gap-3">
              <button 
                onClick={() => scroll('left')}
                className="p-4 rounded-full border border-slate-700 text-white hover:bg-white hover:text-slate-900 transition-all shadow-xl"
                aria-label="Geri"
              >
                <ChevronLeft size={24} />
              </button>
              <button 
                onClick={() => scroll('right')}
                className="p-4 rounded-full border border-slate-700 text-white hover:bg-white hover:text-slate-900 transition-all shadow-xl"
                aria-label="İleri"
              >
                <ChevronRight size={24} />
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Horizontal Scroll Container */}
      <div 
        ref={scrollContainerRef}
        onScroll={handleScroll}
        className={`relative w-full overflow-x-auto pb-12 hide-scrollbar scroll-smooth cursor-grab active:cursor-grabbing ${!isReady ? 'opacity-0' : 'opacity-100 transition-opacity duration-500'}`}
      >
        <div className="flex gap-6 px-4 sm:px-6 lg:px-8 w-max">
          {tripledGroups.map((group, index) => (
            <div key={`${group.id}-${index}`} className="group w-[300px] h-[420px] perspective-1000">
              <div className="relative w-full h-full transition-all duration-700 transform-style-3d group-hover:rotate-y-180">
                
                {/* Front Face */}
                <div className="absolute inset-0 w-full h-full backface-hidden rounded-[2.5rem] overflow-hidden bg-white shadow-xl">
                  <div className="h-2/3 w-full relative overflow-hidden">
                    <img 
                      src={group.image} 
                      alt={group.title} 
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-60" />
                  </div>
                  <div className="h-1/3 p-8 flex flex-col justify-center bg-white border-t border-slate-50">
                    <h3 className="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight leading-tight">{group.title}</h3>
                    <div className="flex items-center gap-2 text-odtu-red font-black text-xs uppercase tracking-widest mt-2">
                      İncele <ArrowRight size={14} />
                    </div>
                  </div>
                </div>

                {/* Back Face */}
                <div className="absolute inset-0 w-full h-full backface-hidden rotate-y-180 rounded-[2.5rem] overflow-hidden bg-odtu-blue text-white p-10 flex flex-col justify-center text-center shadow-xl border-4 border-white/10">
                  <div className="w-16 h-16 bg-white/20 rounded-2xl mx-auto mb-6 flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <span className="text-3xl font-black">O</span>
                  </div>
                  <h3 className="text-2xl font-black mb-4 uppercase tracking-tight">{group.title}</h3>
                  <p className="text-blue-50 leading-relaxed mb-10 text-sm font-medium opacity-90">
                    {group.description}
                  </p>
                  <button className="px-8 py-4 bg-white text-odtu-blue rounded-2xl font-black text-xs hover:bg-odtu-red hover:text-white transition-all uppercase tracking-widest shadow-lg">
                    GRUBA KATIL
                  </button>
                </div>

              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default WorkingGroups;