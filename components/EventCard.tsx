
import React from 'react';
import { Calendar, MapPin, ArrowRight } from 'lucide-react';
import { Event } from '../types';

interface EventCardProps {
  event: Event;
}

const EventCard: React.FC<EventCardProps> = ({ event }) => {
  return (
    <div className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full">
      <div className="relative aspect-square overflow-hidden">
        <div className="absolute top-4 left-4 bg-white/90 backdrop-blur text-[10px] font-bold px-3 py-1 rounded-full text-odtu-red uppercase tracking-wider z-10 shadow-sm">
          {event.category}
        </div>
        <img 
          src={event.image} 
          alt={event.title} 
          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
      </div>
      
      <div className="p-5 flex flex-col flex-1">
        <h3 className="text-lg font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-odtu-red transition-colors min-h-[3.5rem]">
          {event.title}
        </h3>
        
        <div className="flex items-center gap-2 text-xs text-gray-500 mb-1.5">
          <Calendar size={14} className="text-odtu-red" />
          <span>{event.date}</span>
        </div>
        
        <div className="flex items-center gap-2 text-xs text-gray-500 mb-3">
          <MapPin size={14} className="text-odtu-red" />
          <span className="truncate">{event.location}</span>
        </div>
        
        <p className="text-gray-600 text-sm mb-5 line-clamp-2 flex-1">
          {event.description}
        </p>
        
        <button className="mt-auto w-full py-2.5 rounded-lg border border-gray-100 bg-gray-50 font-bold text-xs text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all flex items-center justify-center gap-2 group/btn">
          DETAYLARI İNCELE
          <ArrowRight size={14} className="group-hover/btn:translate-x-1 transition-transform" />
        </button>
      </div>
    </div>
  );
};

export default EventCard;
