
import React, { useState } from 'react';
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight, Camera, ExternalLink } from 'lucide-react';

interface Event {
  day: number;
  title: string;
  image: string;
  recurring?: boolean;
}

const MOCK_EVENTS: Event[] = [
  { day: 1, title: "Hoş Geldin Yeni Yıl!", image: "https://images.unsplash.com/photo-1546706887-9e0b8acd9142?auto=format&fit=crop&q=80&w=600" },
  { day: 5, title: "Maraton Buluşması", image: "https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&q=80&w=600" },
  { day: 11, title: "Şaşkınbakkal Buluşması", image: "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=600" },
  // Recurring Wednesdays (Assuming Jan 2025, 1st is Wed)
  { day: 8, title: "Online Yoga", image: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600", recurring: true },
  { day: 15, title: "Online Yoga", image: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600", recurring: true },
  { day: 22, title: "Online Yoga", image: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600", recurring: true },
  { day: 29, title: "Online Yoga", image: "https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600", recurring: true },
  // Recurring Sundays (Assuming Jan 2025, 5th is Sun)
  { day: 12, title: "Koro Pratiği", image: "https://images.unsplash.com/photo-1526218626217-dc65a29bb444?auto=format&fit=crop&q=80&w=600", recurring: true },
  { day: 19, title: "Koro Pratiği", image: "https://images.unsplash.com/photo-1526218626217-dc65a29bb444?auto=format&fit=crop&q=80&w=600", recurring: true },
  { day: 26, title: "Koro Pratiği", image: "https://images.unsplash.com/photo-1526218626217-dc65a29bb444?auto=format&fit=crop&q=80&w=600", recurring: true },
];

const GALLERY_IMAGES = [
  "https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=800",
  "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=800",
  "https://images.unsplash.com/photo-1475721027187-4024733923f7?auto=format&fit=crop&q=80&w=800",
  "https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800",
  "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&q=80&w=800",
  "https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&q=80&w=800"
];

const EventsPage: React.FC = () => {
  const [hoveredEvent, setHoveredEvent] = useState<Event | null>(null);

  const daysInMonth = 31;
  const startDayOffset = 2; // Assuming Jan starts on Wed (Mon=0, Tue=1, Wed=2)
  const calendarDays = Array.from({ length: 42 }, (_, i) => {
    const dayNum = i - startDayOffset + 1;
    return dayNum > 0 && dayNum <= daysInMonth ? dayNum : null;
  });

  const getEventsForDay = (day: number) => MOCK_EVENTS.filter(e => e.day === day);

  return (
    <div className="bg-slate-50 min-h-screen pt-32 pb-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="text-center mb-16">
          <div className="inline-flex items-center gap-2 px-4 py-2 bg-odtu-red/10 text-odtu-red rounded-full text-xs font-black uppercase tracking-widest mb-4">
            <CalendarIcon size={16} />
            Etkinlik Takvimi
          </div>
          <h1 className="text-4xl md:text-6xl font-black text-slate-900 mb-6 uppercase tracking-tighter">
            OCAK <span className="text-odtu-blue">2025</span>
          </h1>
          <p className="text-slate-500 max-w-2xl mx-auto font-medium">
            İstanbul ODTÜ Mezunlar Derneği olarak her hafta onlarca sosyal, kültürel ve sportif etkinlikte buluşuyoruz. Takvimdeki etkinliklere tıklayarak detaylara ulaşabilirsiniz.
          </p>
        </div>

        {/* Calendar Grid */}
        <div className="relative mb-24">
          {/* Hover Preview Tooltip */}
          {hoveredEvent && (
            <div className="fixed z-[100] pointer-events-none transition-opacity duration-300 transform -translate-x-1/2 -translate-y-full mb-4 animate-slide-up"
                 style={{ left: '50%', top: '40%' }}>
              <div className="bg-white p-2 rounded-2xl shadow-2xl border-4 border-odtu-red overflow-hidden w-64">
                <img src={hoveredEvent.image} alt={hoveredEvent.title} className="w-full h-40 object-cover rounded-xl mb-2" />
                <p className="text-center font-bold text-slate-800 text-sm">{hoveredEvent.title}</p>
              </div>
            </div>
          )}

          <div className="bg-white rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden">
            {/* Days Header */}
            <div className="grid grid-cols-7 bg-slate-900 text-white text-center py-6 font-black text-xs uppercase tracking-[0.2em]">
              {['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'].map(d => <div key={d}>{d}</div>)}
            </div>

            {/* Calendar Cells */}
            <div className="grid grid-cols-7 border-l border-t border-slate-100">
              {calendarDays.map((day, idx) => {
                const dayEvents = day ? getEventsForDay(day) : [];
                return (
                  <div key={idx} className={`min-h-[140px] p-4 border-r border-b border-slate-100 flex flex-col transition-colors ${day ? 'bg-white' : 'bg-slate-50/50'}`}>
                    {day && (
                      <>
                        <span className={`text-lg font-black mb-2 ${[5, 6].includes(idx % 7) ? 'text-odtu-red' : 'text-slate-400'}`}>
                          {day}
                        </span>
                        <div className="space-y-1.5 overflow-y-auto hide-scrollbar">
                          {dayEvents.map((event, eIdx) => (
                            <a
                              key={eIdx}
                              href="https://fonzip.com/odtumist/events"
                              target="_blank"
                              rel="noreferrer"
                              onMouseEnter={() => setHoveredEvent(event)}
                              onMouseLeave={() => setHoveredEvent(null)}
                              className={`block px-2 py-1.5 rounded-lg text-[10px] font-bold leading-tight transition-all hover:scale-105 shadow-sm border ${
                                event.recurring 
                                  ? 'bg-odtu-blue/5 text-odtu-blue border-odtu-blue/20 hover:bg-odtu-blue hover:text-white' 
                                  : 'bg-odtu-red/5 text-odtu-red border-odtu-red/20 hover:bg-odtu-red hover:text-white'
                              }`}
                            >
                              {event.title}
                            </a>
                          ))}
                        </div>
                      </>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
          
          <div className="flex justify-center mt-10 gap-8">
             <div className="flex items-center gap-2 text-xs font-bold text-slate-500">
               <div className="w-3 h-3 rounded-full bg-odtu-red" /> Özel Etkinlikler
             </div>
             <div className="flex items-center gap-2 text-xs font-bold text-slate-500">
               <div className="w-3 h-3 rounded-full bg-odtu-blue" /> Düzenli Atölyeler
             </div>
          </div>
        </div>

        {/* Gallery Section */}
        <section className="mt-32">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
              <div className="inline-flex items-center gap-2 text-odtu-blue font-black uppercase tracking-widest text-xs mb-4">
                <Camera size={18} />
                Vizörden ODTÜMİST
              </div>
              <h2 className="text-3xl md:text-5xl font-black text-slate-900 uppercase tracking-tighter">
                ETKİNLİKLERDEN <span className="text-odtu-red">KARELER</span>
              </h2>
            </div>
            <button className="px-8 py-4 bg-slate-900 text-white rounded-full font-black text-sm hover:bg-odtu-red transition-all flex items-center gap-2 shadow-xl group">
              TÜMÜNÜ GÖR
              <ChevronRight size={18} className="group-hover:translate-x-1 transition-transform" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {GALLERY_IMAGES.map((img, idx) => (
              <div key={idx} className="group relative aspect-video rounded-[2rem] overflow-hidden shadow-lg border-4 border-white hover:border-odtu-red transition-all duration-500">
                <img src={img} alt="Etkinlik Karesi" className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                   <div className="w-12 h-12 bg-white rounded-full flex items-center justify-center text-slate-900">
                     <ExternalLink size={20} />
                   </div>
                </div>
              </div>
            ))}
          </div>
        </section>

      </div>
    </div>
  );
};

export default EventsPage;
