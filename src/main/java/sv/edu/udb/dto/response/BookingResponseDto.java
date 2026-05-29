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
public class BookingResponseDto {
    private Integer idBooking;
    private Integer eventId;
    private String eventTitle;
    private Integer userId;
    private String username;
    private Integer quantity;
    private BigDecimal totalAmount;
    private LocalDateTime bookingDate;
    private String status;
}
