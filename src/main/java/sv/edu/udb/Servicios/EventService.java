package sv.edu.udb.Servicios;

import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import sv.edu.udb.Modelos.Event;
import sv.edu.udb.Repositorios.EventRepository;
import org.springframework.stereotype.Service;
import java.util.List;

@RequiredArgsConstructor
@Service
public class EventService {
    private final EventRepository eventRepository;

    public List<Event> listarEventos() {
        return eventRepository.findAll();
    }

    public Page<Event> listarEventosPaginados(Pageable pageable) {
        return eventRepository.findAll(pageable);
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
        event.setDescription(eventDetails.getDescription());
        event.setEventDate(eventDetails.getEventDate());
        event.setVenue(eventDetails.getVenue());
        event.setCapacity(eventDetails.getCapacity());
        event.setPricePerTicket(eventDetails.getPricePerTicket());
        return eventRepository.save(event);
    }

    public void eliminarEvento(Integer id) {
        Event event = eventRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Evento no encontrado"));
        eventRepository.delete(event);
    }
}