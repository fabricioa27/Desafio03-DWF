package sv.edu.udb.DTOS;

import  lombok.Data;

@Data
public class BookingRequestDTO {
    private Integer eventId;
    private Integer quantity;
}
