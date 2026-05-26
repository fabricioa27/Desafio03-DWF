package sv.edu.udb.Modelos;

import jakarta.persistence.*;
import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table
@Data
public class Booking {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer idBooking;
    @ManyToOne @JoinColumn(name = "event_id")
    private Event event;
    @ManyToOne @JoinColumn(name = "user_id")
    private User user;
    private Integer quantity;
    private BigDecimal totalAmount; // Para el calculo automático
    private LocalDateTime bookingDate;
    @Enumerated(EnumType.STRING)
    private  BookingStatus status;
}
