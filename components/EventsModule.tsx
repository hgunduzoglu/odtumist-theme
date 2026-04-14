
import React, { useState, useRef } from 'react';
import { FEATURED_EVENTS } from '../constants';
import EventCard from './EventCard';
import { ChevronLeft, ChevronRight, ArrowRight, CalendarDays } from 'lucide-react';
import { ViewState } from '../types';

interface EventsModuleProps {
  onNavigate?: (view: ViewState) => void;
}

const CATEGORIES = ['Tümü', 'Sosyal', 'Fotoğraf', 'Edebiyat', 'Burs', 'Spor', 'Kültür', 'Söyleşi'];

const EventsModule: React.FC<EventsModuleProps> = ({ onNavigate }) => {
  const [activeCategory, setActiveCategory] = useState('Tümü');
  const scrollContainerRef = useRef<HTMLDivElement>(null);

  const filteredEvents = activeCategory === 'Tümü' 
    ? FEATURED_EVENTS 
    : FEATURED_EVENTS.filter(e => e.category === activeCategory);

  const scroll = (direction: 'left' | 'right') => {
    if (scrollContainerRef.current) {
      const { scrollLeft, clientWidth } = scrollContainerRef.current;
      const scrollTo = direction === 'left' ? scrollLeft - clientWidth : scrollLeft + clientWidth;
      scrollContainerRef.current.scrollTo({ left: scrollTo, behavior: 'smooth' });
    }
  };

  return (
    <section className="py-24 bg-white overflow-hidden">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
          <div className="max-w-2xl">
            <button 
              onClick={() => onNavigate?.(ViewState.EVENTS)}
              className="flex items-center gap-2 mb-6 px-4 py-2 bg-slate-50 text-odtu-red rounded-full border border-slate-100 hover:bg-odtu-red hover:text-white transition-all group"
            >
              <CalendarDays size={18} />
              <span className="font-bold uppercase tracking-widest text-xs">Etkinlik Takvimini Görüntüle</span>
              <ArrowRight size={14} className="group-hover:translate-x-1 transition-transform" />
            </button>
            <h2 className="text-3xl md:text-5xl font-extrabold text-slate-900 mb-4 uppercase tracking-tighter">Etkinliklerimiz</h2>
            <p className="text-gray-600 text-lg leading-relaxed">
              İlgi alanlarınıza göre etkinlikleri filtreleyebilir, İstanbul'daki ODTÜ ruhunu yaşatan buluşmalara katılabilirsiniz.
            </p>
          </div>

          <div className="flex flex-col items-end gap-4">
             <div className="flex gap-3">
              <button 
                onClick={() => scroll('left')}
                className="p-3 rounded-full border border-slate-200 text-slate-400 hover:border-odtu-red hover:text-odtu-red transition-all"
              >
                <ChevronLeft size={24} />
              </button>
              <button 
                onClick={() => scroll('right')}
                className="p-3 rounded-full border border-slate-200 text-slate-400 hover:border-odtu-red hover:text-odtu-red transition-all"
              >
                <ChevronRight size={24} />
              </button>
            </div>
            {/* Visual Indicator of scrollable content */}
            <div className="w-24 h-1 bg-slate-100 rounded-full overflow-hidden">
              <div className="h-full bg-odtu-red w-1/3 animate-pulse" />
            </div>
          </div>
        </div>

        {/* Filter Tabs */}
        <div className="flex flex-wrap gap-2 mb-12">
          {CATEGORIES.map(cat => (
            <button
              key={cat}
              onClick={() => setActiveCategory(cat)}
              className={`px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300 border-2 ${
                activeCategory === cat
                  ? 'bg-odtu-blue text-white border-odtu-blue shadow-lg shadow-blue-900/20'
                  : 'bg-white text-slate-500 border-slate-100 hover:border-odtu-blue hover:text-odtu-blue'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>
      </div>

      {/* Scrollable Container */}
      <div className="relative w-full">
        <div 
          ref={scrollContainerRef}
          className="flex overflow-x-auto gap-6 px-4 sm:px-6 lg:px-[calc((100vw-1280px)/2+32px)] scroll-smooth hide-scrollbar pb-6"
        >
          {filteredEvents.length > 0 ? (
            filteredEvents.map(event => (
              <div key={event.id} className="w-[280px] md:w-[350px] flex-shrink-0">
                <EventCard event={event} />
              </div>
            ))
          ) : (
            <div className="w-full py-20 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center px-8">
              <p className="text-slate-400 font-medium text-lg">Bu kategoride şu an planlanmış bir etkinlik bulunmuyor.</p>
            </div>
          )}
        </div>
      </div>
    </section>
  );
};

export default EventsModule;
