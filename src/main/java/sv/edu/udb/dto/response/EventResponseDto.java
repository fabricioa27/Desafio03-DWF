package sv.edu.udb.dto.response;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class EventResponseDto {
    private Integer idEvent;
    private String title;
    private String description;
    private LocalDateTime eventDate;
    private String venue;
    private Integer capacity;
    private BigDecimal pricePerTicket;
}
