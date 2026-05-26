package sv.edu.udb.Servicios;

import sv.edu.udb.DTOS.*;
import sv.edu.udb.Modelos.*;
import sv.edu.udb.Repositorios.*;
import org.springframework.stereotype.Service;
import java.math.BigDecimal;

@Service
public class BookingService {
    private final BookingRepository bookingRepository;
    private final EventRepository eventRepository;

    public BookingService(BookingRepository br, EventRepository er) {
        this.bookingRepository = br;
        this.eventRepository = er;
    }

    public Booking crearReserva(BookingRequestDTO dto, User usuario) {
        Event evento = eventRepository.findById(dto.getEventId())
                .orElseThrow(() -> new RuntimeException("Evento no encontrado"));

        // Logica para calcular el total
        double total = evento.getPricePerTicket().doubleValue() * dto.getQuantity();

        Booking booking = new Booking();
        booking.setEvent(evento);
        booking.setUser(usuario);
        booking.setQuantity(dto.getQuantity());
        booking.setTotalAmount(BigDecimal.valueOf(total));
        booking.setStatus(BookingStatus.CONFIRMED);

        return bookingRepository.save(booking);
    }
}
