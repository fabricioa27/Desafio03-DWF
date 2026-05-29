package sv.edu.udb.Servicios;

import lombok.RequiredArgsConstructor;
import sv.edu.udb.dto.request.BookingRequestDto;
import sv.edu.udb.Modelos.*;
import sv.edu.udb.Repositorios.*;
import org.springframework.stereotype.Service;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.List;

@RequiredArgsConstructor
@Service
public class BookingService {
    private final BookingRepository bookingRepository;
    private final EventRepository eventRepository;

    public Booking crearReserva(BookingRequestDto dto, User usuario) {
        Event evento = eventRepository.findById(dto.getEventId())
                .orElseThrow(() -> new RuntimeException("Evento no encontrado"));

        // Validar que la cantidad solicitada no exceda la capacidad del evento
        if (dto.getQuantity() > evento.getCapacity()) {
            throw new RuntimeException("La cantidad de tickets solicitada excede la capacidad del evento. Capacidad disponible: " + evento.getCapacity());
        }

        // Calculo preciso con BigDecimal
        BigDecimal cantidad = BigDecimal.valueOf(dto.getQuantity());
        BigDecimal total = evento.getPricePerTicket().multiply(cantidad);

        Booking booking = new Booking();
        booking.setEvent(evento);
        booking.setUser(usuario);
        booking.setQuantity(dto.getQuantity());
        booking.setTotalAmount(total);
        booking.setBookingDate(LocalDateTime.now());
        booking.setStatus(BookingStatus.CONFIRMED);

        // Actualizar capacidad del evento
        evento.setCapacity(evento.getCapacity() - dto.getQuantity());
        eventRepository.save(evento);

        return bookingRepository.save(booking);
    }

    public List<Booking> listarMisReservas(User usuario) {
        return bookingRepository.findByUser(usuario);
    }

    public void cancelarReserva(Integer id) {
        Booking booking = bookingRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Reserva no encontrada"));
        
        // Solo restaurar capacidad si la reserva estaba confirmada
        if (booking.getStatus() == BookingStatus.CONFIRMED) {
            Event evento = booking.getEvent();
            evento.setCapacity(evento.getCapacity() + booking.getQuantity());
            eventRepository.save(evento);
        }
        
        booking.setStatus(BookingStatus.CANCELLED);
        bookingRepository.save(booking);
    }
}