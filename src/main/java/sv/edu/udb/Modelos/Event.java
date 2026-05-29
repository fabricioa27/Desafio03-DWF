package sv.edu.udb.Modelos;

import jakarta.persistence.*;
import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table (name = "events")
@Data
public class Event {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer idEvent;
    private String title;
    private String description;
    private LocalDateTime eventDate;
    private String venue;
    private Integer capacity;
    private BigDecimal pricePerTicket;
}
