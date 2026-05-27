package sv.edu.udb.Servicios;

import sv.edu.udb.Modelos.Event;
import sv.edu.udb.Repositorios.EventRepository;
import org.springframework.stereotype.Service;
import java.util.List;

@Service
public class EventService{
    private final EventRepository eventRepository;

    public EventService(EventRepository eventRepository) {
        this.eventRepository = eventRepository;
    }

    public List<Event> listarEventos() {
        return eventRepository.findAll();
    }

    public Event obtenerPorId(Integer id) {
        return eventRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Evento no encontrado"));
    }

    public Event crearEvento(Event event) {
        return eventRepository.save(event);
    }

    public Event actualizarEvento(Integer id, Event eventDetails) {
        Event event = obtenerPorId(id);
        event.setTitle(eventDetails.getTitle());
        event.setEventDate(eventDetails.getEventDate());
        event.setVenue(eventDetails.getVenue());
        event.setCapacity(eventDetails.getCapacity());
        event.setPricePerTicket(eventDetails.getPricePerTicket());
        return eventRepository.save(event);
    }

    public void eliminarEvento(Integer id) {
        eventRepository.deleteById(id);
    }
}