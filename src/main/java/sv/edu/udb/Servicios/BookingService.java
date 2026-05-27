package sv.edu.udb.Servicios;

import sv.edu.udb.DTOS.*;
import sv.edu.udb.Modelos.*;
import sv.edu.udb.Repositorios.*;
import org.springframework.stereotype.Service;
import java.math.BigDecimal;
import java.util.List;

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

        // Calculo preciso con BigDecimal [cite: 43]
        BigDecimal cantidad = BigDecimal.valueOf(dto.getQuantity());
        BigDecimal total = evento.getPricePerTicket().multiply(cantidad);

        Booking booking = new Booking();
        booking.setEvent(evento);
        booking.setUser(usuario);
        booking.setQuantity(dto.getQuantity());
        booking.setTotalAmount(total);
        booking.setStatus(BookingStatus.CONFIRMED);

        return bookingRepository.save(booking);
    }

    public List<Booking> listarMisReservas(User usuario) {
        return bookingRepository.findByUser(usuario);
    }

    public void cancelarReserva(Integer id) {
        Booking booking = bookingRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Reserva no encontrada"));
        booking.setStatus(BookingStatus.CANCELLED); // Lógica de cancelación [cite: 46]
        bookingRepository.save(booking);
    }
}